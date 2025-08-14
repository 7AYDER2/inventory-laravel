<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryServices
{
    public function stockIn(Product $product, int $qty, ?string $refType=null, ?int $refId=null, ?string $note=null)
    {
        return DB::transaction(function () use ($product,$qty,$refType,$refId,$note){
            $product->increment('quantity_in_stock', $qty);

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity_change' => $qty,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'note' => $note,
            ]);
        });
    }

    public function stockOut(Product $product, int $qty, ?string $refType=null, ?int $refId=null, ?string $note=null)
    {
        return DB::transaction(function () use ($product,$qty,$refType,$refId,$note){
            if ($product->quantity_in_stock < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock for product '.$product->sku,
                ]);
            }

            $product->decrement('quantity_in_stock', $qty);

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity_change' => -$qty,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'note' => $note,
            ]);
        });
    }

    public function adjust(Product $product, int $delta, ?string $note=null)
    {
        return DB::transaction(function () use ($product,$delta,$note){
            $new = $product->quantity_in_stock + $delta;
            if ($new < 0) {
                throw ValidationException::withMessages(['quantity' => 'Adjustment would make stock negative.']);
            }
            $product->update(['quantity_in_stock' => $new]);

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity_change' => $delta,
                'note' => $note,
            ]);
        });
    }
}
