<?php

namespace App\Services;

use App\Models\EnvelopeType;
use App\Models\Product;
use App\Models\SheetType;
use App\Models\StockPurchase;
use App\Models\StockPurchaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockPurchaseService
{
    public static function purchasableClass(string $type): string
    {
        return match ($type) {
            'product' => Product::class,
            'sheet_type' => SheetType::class,
            'envelope_type' => EnvelopeType::class,
            default => throw new InvalidArgumentException('Invalid item type'),
        };
    }

    /**
     * @param  array{supplier_id: int, notes?: string|null, purchased_at: string, payment_method: string, discount?: float, deduction?: float, additional_charges?: float}  $data
     * @param  array<int, array{item_type: string, item_id: int, quantity: int, unit_cost?: float|null}>  $lines
     */
    public function recordPurchase(array $data, array $lines): StockPurchase
    {
        return DB::transaction(function () use ($data, $lines) {
            $supplier = Supplier::query()->findOrFail($data['supplier_id']);

            $subtotal = $this->sumLineSubtotal($lines);
            $discount = round((float) ($data['discount'] ?? 0), 4);
            $deduction = round((float) ($data['deduction'] ?? 0), 4);
            $additionalCharges = round((float) ($data['additional_charges'] ?? 0), 4);
            $totalCost = $this->computeTotalCost($subtotal, $discount, $deduction, $additionalCharges);

            $purchase = StockPurchase::create([
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'notes' => $data['notes'] ?? null,
                'purchased_at' => $data['purchased_at'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'deduction' => $deduction,
                'additional_charges' => $additionalCharges,
                'total_cost' => $totalCost,
                'user_id' => auth()->id(),
            ]);

            $purchase->update([
                'reference' => 'PO-'.str_pad((string) $purchase->id, 6, '0', STR_PAD_LEFT),
            ]);

            foreach ($lines as $line) {
                $class = self::purchasableClass($line['item_type']);
                $model = $class::query()->findOrFail($line['item_id']);

                StockPurchaseItem::create([
                    'stock_purchase_id' => $purchase->id,
                    'purchasable_type' => $model::class,
                    'purchasable_id' => $model->getKey(),
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'] ?? null,
                ]);

                $model->increment('stock_quantity', $line['quantity']);
            }

            return $purchase->load('items.purchasable');
        });
    }

    /**
     * @param  array<int, array{quantity: int, unit_cost?: float|null}>  $lines
     */
    public function sumLineSubtotal(array $lines): float
    {
        $sum = 0.0;
        foreach ($lines as $line) {
            $uc = $line['unit_cost'] ?? null;
            if ($uc !== null) {
                $sum += (int) $line['quantity'] * (float) $uc;
            }
        }

        return round($sum, 4);
    }

    public function computeTotalCost(float $subtotal, float $discount, float $deduction, float $additionalCharges): float
    {
        $total = $subtotal - $discount - $deduction + $additionalCharges;

        return round(max(0, $total), 4);
    }
}
