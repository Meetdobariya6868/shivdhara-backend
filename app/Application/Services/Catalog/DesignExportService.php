<?php

declare(strict_types=1);

namespace App\Application\Services\Catalog;

use App\Models\Design;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Builds the catalogue export workbook: one row per design variant, carrying
 * its company, design, code and rates. Kept separate from CatalogService so the
 * spreadsheet-format concern lives in one place (mirrors how QuotationService
 * owns the PDF format).
 */
final class DesignExportService
{
    /** @var list<string> */
    private const HEADINGS = [
        'Company',
        'Design Name',
        'Code',
        'Size',
        'Finish',
        'Thickness',
        'Purchase Rate',
        'Sell Rate',
    ];

    /**
     * Render the given designs (with company + variants eager-loaded) to an
     * .xlsx binary string, ready to stream as a download.
     *
     * @param  Collection<int, Design>  $designs
     */
    public function toXlsx(Collection $designs): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Designs');

        $sheet->fromArray(self::HEADINGS, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($designs as $design) {
            foreach ($design->variants as $variant) {
                $sheet->fromArray([
                    $design->company?->company_name ?? '',
                    $design->design_name,
                    $variant->code,
                    $variant->size,
                    $variant->finish,
                    $variant->thickness,
                    (float) $variant->purchase_rate,
                    (float) $variant->sell_rate,
                ], null, 'A'.$row);
                $row++;
            }
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }
}
