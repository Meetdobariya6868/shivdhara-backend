<?php

declare(strict_types=1);

namespace App\Application\Services\Order;

use App\Domain\Enums\ItemType;
use App\Models\DesignVariant;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

/**
 * Renders an order quotation PDF (barryvdh/laravel-dompdf).
 *
 * Two modes share one layout: "name" prints each product's design name + finish,
 * "code" prints the design code. The order graph must already be loaded (rooms →
 * items → designVariant.design, customer, creator) so no query runs here.
 */
final class QuotationService
{
    /** @var list<string> */
    public const MODES = ['name', 'code'];

    /**
     * @param  'name'|'code'  $mode
     */
    public function render(Order $order, string $mode): DomPdf
    {
        return Pdf::loadView('pdf.quotation', $this->buildViewData($order, $mode))
            ->setPaper('a4');
    }

    /**
     * Build a temporary, signed, publicly-openable URL to this order's quotation
     * PDF for sharing outside the app (e.g. WhatsApp). The signature makes the
     * link tamper-proof (the order id and format are signed) and it expires after
     * the configured TTL, after which it must be regenerated from inside the app.
     *
     * @param  'name'|'code'  $mode
     * @return array{url: string, expires_at: string}
     */
    public function shareUrl(Order $order, string $mode): array
    {
        $expiresAt = Carbon::now()->addDays(
            (int) config('quotation.share_link_ttl_days', 30),
        );

        $url = URL::temporarySignedRoute(
            'api.v1.orders.quotation.shared',
            $expiresAt,
            ['order' => $order->id, 'format' => $mode],
        );

        return ['url' => $url, 'expires_at' => $expiresAt->toISOString()];
    }

    /**
     * @param  'name'|'code'  $mode
     * @return array<string, mixed>
     */
    private function buildViewData(Order $order, string $mode): array
    {
        // A room named "0" is a sentinel for "this order has no rooms": its header
        // row is suppressed and its items are numbered sequentially (1, 2, 3…) in
        // the Sr.No column. Any other room name keeps the grouped layout — a red
        // header row per room, with items left unnumbered underneath.
        $itemSr = 0;

        $rooms = $order->rooms->map(function ($room, int $index) use ($mode, &$itemSr): array {
            $flat = trim((string) $room->room_name) === '0';

            return [
                'flat' => $flat,
                'sr' => $index + 1,
                'name' => $room->room_name,
                'items' => $room->items->map(function ($item) use ($mode, $flat, &$itemSr): array {
                    $row = $this->itemRow($item, $mode);
                    $row['sr'] = $flat ? ++$itemSr : null;

                    return $row;
                })->all(),
            ];
        })->all();

        $freight = (float) $order->transportation_charge;
        $paid = (float) $order->advance_payment;
        $grandTotal = (float) $order->grand_total;

        return [
            'company' => config('quotation'),
            'logo' => $this->logoDataUri(),
            'mode' => $mode,
            'date' => $order->created_at?->format('d/m/Y') ?? '',
            'client' => [
                'name' => $order->customer?->name ?? '',
                'contact' => $order->customer?->contact ?? '',
            ],
            'salesManager' => [
                'name' => $order->creator?->name ?? '',
                'contact' => $order->creator?->mobile_number ?? '',
            ],
            'architectName' => $order->architect_name,
            'rooms' => $rooms,
            'totals' => [
                'freight' => $freight,
                'paid' => $paid,
                'payable' => round($grandTotal - $paid, 2),
                'grand_total' => $grandTotal,
            ],
        ];
    }

    /**
     * @param  'name'|'code'  $mode
     * @return array<string, mixed>
     */
    private function itemRow(mixed $item, string $mode): array
    {
        $variant = $item->designVariant;
        $design = $variant?->design;

        // QTY display: a box item shows its box count with pieces-per-box in
        // parentheses — "10 (5)" = 10 boxes of 5 pieces each; a piece item shows
        // just its piece count.
        $isBox = $item->item_type === ItemType::Box;

        return [
            'size' => $this->sizeLabel($variant),
            // "code" mode prints the variant's own product code (a quote line is
            // always a specific variant); "name" mode prints "Company - Design".
            'name' => $mode === 'code'
                ? ($variant?->code ?? '-')
                : $this->designLabel($design),
            // Finish is shown only in "name" mode, under the design name (as printed).
            'finish' => $mode === 'name' ? ($variant?->finish ?? '') : '',
            'image' => $this->imageDataUri($item->product_image_path),
            'sqft_rate' => (float) $item->sqft_rate,
            'rate_pcs' => (float) $item->price_per_item,
            'qty' => (int) $item->quantity,
            'per_box' => $isBox ? (int) $item->pieces_per_box : null,
            'amount' => (float) $item->product_total,
        ];
    }

    /**
     * "Company - Design" for the Design Name column. Falls back to just the
     * design name when the company is missing, and to "-" when both are absent.
     */
    private function designLabel(mixed $design): string
    {
        $designName = trim((string) ($design?->design_name ?? ''));
        $companyName = trim((string) ($design?->company?->company_name ?? ''));

        if ($companyName !== '' && $designName !== '') {
            return "{$companyName} - {$designName}";
        }

        return $designName !== '' ? $designName : ($companyName !== '' ? $companyName : '-');
    }

    /** "24x24 (9)" — variant size plus the numeric thickness in parentheses. */
    private function sizeLabel(?DesignVariant $variant): string
    {
        if ($variant === null) {
            return '-';
        }

        $thickness = preg_replace('/\D+/', '', (string) $variant->thickness) ?? '';

        return $variant->size.($thickness !== '' ? " ({$thickness})" : '');
    }

    /**
     * Base64 data URI for a product image. Two path shapes are stored (see
     * App\Support\ProductImage): legacy absolute URLs, fetched once over HTTP and
     * embedded; and relative paths, read from the public disk. Both are inlined
     * as data URIs so dompdf stays self-contained. Any failure (missing file,
     * unreachable host, timeout) returns null so the row falls back to an empty
     * thumbnail instead of breaking the whole PDF.
     */
    private function imageDataUri(?string $path): ?string
    {
        $path = $path !== null ? trim($path) : '';
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $this->remoteImageDataUri($path);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    /**
     * Fetch a legacy absolute-URL image and inline it. Spaces are encoded so the
     * request URL is valid; a short timeout keeps one slow image from stalling
     * the whole quotation.
     */
    private function remoteImageDataUri(string $url): ?string
    {
        try {
            $response = Http::timeout(5)->get(str_replace(' ', '%20', $url));
            if (! $response->successful()) {
                return null;
            }

            $mime = $response->header('Content-Type') ?: 'image/jpeg';

            return 'data:'.$mime.';base64,'.base64_encode($response->body());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Base64 data URI for the company letterhead logo. Bundled as a static app
     * asset (not a user upload) and embedded so dompdf stays self-contained.
     */
    private function logoDataUri(): ?string
    {
        $path = public_path('images/quotation-logo.png');
        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }
}
