<?php

declare(strict_types=1);

namespace App\Application\Services\Order;

use App\Domain\Enums\ItemType;
use App\Models\DesignVariant;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\Storage;

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
                'flat'  => $flat,
                'sr'    => $index + 1,
                'name'  => $room->room_name,
                'items' => $room->items->map(function ($item) use ($mode, $flat, &$itemSr): array {
                    $row = $this->itemRow($item, $mode);
                    $row['sr'] = $flat ? ++$itemSr : null;

                    return $row;
                })->all(),
            ];
        })->all();

        $freight    = (float) $order->transportation_charge;
        $paid       = (float) $order->advance_payment;
        $grandTotal = (float) $order->grand_total;

        return [
            'company'      => config('quotation'),
            'mode'         => $mode,
            'date'         => $order->created_at?->format('d/m/Y') ?? '',
            'client'       => [
                'name'    => $order->customer?->name ?? '',
                'contact' => $order->customer?->contact ?? '',
            ],
            'salesManager' => [
                'name'    => $order->creator?->name ?? '',
                'contact' => $order->creator?->mobile_number ?? '',
            ],
            'architectName' => $order->architect_name,
            'rooms'         => $rooms,
            'totals'        => [
                'freight'     => $freight,
                'paid'        => $paid,
                'payable'     => round($grandTotal - $paid, 2),
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
        $design  = $variant?->design;

        // QTY display: a box item shows its box count with pieces-per-box in
        // parentheses — "10 (5)" = 10 boxes of 5 pieces each; a piece item shows
        // just its piece count.
        $isBox = $item->item_type === ItemType::Box;

        return [
            'size'      => $this->sizeLabel($variant),
            'name'      => $mode === 'code'
                ? ($design?->design_code ?? '-')
                : ($design?->design_name ?? '-'),
            // Finish is shown only in "name" mode, under the design name (as printed).
            'finish'    => $mode === 'name' ? ($variant?->finish ?? '') : '',
            'image'     => $this->imageDataUri($item->product_image_path),
            'sqft_rate' => (float) $item->sqft_rate,
            'rate_pcs'  => (float) $item->price_per_item,
            'qty'       => (int) $item->quantity,
            'per_box'   => $isBox ? (int) $item->pieces_per_box : null,
            'amount'    => (float) $item->product_total,
        ];
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
     * Base64 data URI for a product image, read from the public disk. Embedding
     * (vs a remote URL) keeps dompdf self-contained — no network fetch needed.
     */
    private function imageDataUri(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }
}
