<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InventoryServices;

class SaleController extends Controller
{
    public function store(Request $request, InventoryServices $inv)
    {
        $data = $request->validate([
            'customer_id'=>'nullable|exists:customers,id',
            'reference'=>'required|string|unique:sales,reference',
            'sold_at'=>'required|date',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|integer|min:1',
            'items.*.unit_price'=>'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data,$inv){
            $sale = Sale::create([
                'customer_id'=>$data['customer_id'] ?? null,
                'reference'=>$data['reference'],
                'sold_at'=>$data['sold_at'],
                'total'=>0,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $lineTotal = $row['quantity'] * $row['unit_price'];
                $item = SaleItem::create([
                    'sale_id'=>$sale->id,
                    'product_id'=>$row['product_id'],
                    'quantity'=>$row['quantity'],
                    'unit_price'=>$row['unit_price'],
                    'line_total'=>$lineTotal,
                ]);
                $total += $lineTotal;

                $inv->stockOut($item->product, $item->quantity, 'sale', $sale->id, "Sale {$sale->reference}");
            }

            $sale->update(['total'=>$total]);
            return response()->json($sale->load('items'), 201);
        });
    }

    public function show(Sale $sale)
    {
        return $sale->load('items.product','customer');
    }
}