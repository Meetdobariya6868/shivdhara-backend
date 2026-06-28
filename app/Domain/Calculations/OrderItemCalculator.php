<?php

declare(strict_types=1);

namespace App\Domain\Calculations;

use App\Domain\Enums\ItemType;
use App\Domain\Enums\MeasurementUnit;

/**
 * Pure, framework-free calculator for a single order item.
 *
 * All financial figures stored on an order item are derived here from the raw
 * user inputs so the maths lives in exactly one tested place — never trusting
 * numbers computed on the client.
 *
 *   area_sqft      = (height × unit→ft) × (width × unit→ft)
 *   total_pieces   = box   → boxes × pieces_per_box
 *                    piece → number_of_pieces
 *   total_sqft     = area_sqft × total_pieces
 *   purchase_amount= total_sqft × purchase_rate   (rate is per sq ft)
 *   sell_amount    = total_sqft × sell_rate
 *   profit         = sell_amount − purchase_amount
 */
final class OrderItemCalculator
{
    /**
     * @return array{
     *     area_sqft: float,
     *     total_pieces: int,
     *     total_sqft: float,
     *     purchase_amount: float,
     *     sell_amount: float,
     *     profit: float
     * }
     */
    public function calculate(
        ItemType $type,
        ?int $piecesPerBox,
        ?int $numberOfBoxes,
        ?int $numberOfPieces,
        MeasurementUnit $unit,
        float $height,
        float $width,
        float $purchaseRate,
        float $sellRate,
    ): array {
        $factor = $unit->toFeetFactor();

        $areaSqft = round(($height * $factor) * ($width * $factor), 4);

        $totalPieces = $type === ItemType::Box
            ? (int) $numberOfBoxes * (int) $piecesPerBox
            : (int) $numberOfPieces;

        $totalSqft      = round($areaSqft * $totalPieces, 4);
        $purchaseAmount = round($totalSqft * $purchaseRate, 2);
        $sellAmount     = round($totalSqft * $sellRate, 2);
        $profit         = round($sellAmount - $purchaseAmount, 2);

        return [
            'area_sqft'       => $areaSqft,
            'total_pieces'    => $totalPieces,
            'total_sqft'      => $totalSqft,
            'purchase_amount' => $purchaseAmount,
            'sell_amount'     => $sellAmount,
            'profit'          => $profit,
        ];
    }
}
