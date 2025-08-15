<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\InventoryServices;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function store(StorePurchaseRequest $request, InventoryServices $inv)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $inv) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'reference' => $data['reference'],
                'purchased_at' => $data['purchased_at'],
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $line = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'],
                    'line_total' => $row['quantity'] * $row['unit_cost'],
                ]);
                $total += $line->line_total;

                $inv->stockIn($line->product, $line->quantity, 'purchase', $purchase->id, "Purchase {$purchase->reference}");
            }

            $purchase->update(['total' => $total]);
            return new PurchaseResource($purchase->load('items'));
        });
    }

    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase->load('items.product', 'supplier'));
    }
}
