<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InventoryServices;

class PurchaseController extends Controller
{
    public function store(Request $request, InventoryServices $inv)
    {
        $data = $request->validate([
            'supplier_id'=>'required|exists:suppliers,id',
            'reference'=>'required|string|unique:purchases,reference',
            'purchased_at'=>'required|date',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|integer|min:1',
            'items.*.unit_cost'=>'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data,$inv){
            $purchase = Purchase::create([
                'supplier_id'=>$data['supplier_id'],
                'reference'=>$data['reference'],
                'purchased_at'=>$data['purchased_at'],
                'total'=>0,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $line = PurchaseItem::create([
                    'purchase_id'=>$purchase->id,
                    'product_id'=>$row['product_id'],
                    'quantity'=>$row['quantity'],
                    'unit_cost'=>$row['unit_cost'],
                    'line_total'=>$row['quantity']*$row['unit_cost'],
                ]);
                $total += $line->line_total;

                $inv->stockIn($line->product, $line->quantity, 'purchase', $purchase->id, "Purchase {$purchase->reference}");
            }

            $purchase->update(['total'=>$total]);
            return response()->json($purchase->load('items'), 201);
        });
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('items.product','supplier');
    }
}
