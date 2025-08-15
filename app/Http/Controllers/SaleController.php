<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\InventoryServices;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(StoreSaleRequest $request, InventoryServices $inv)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $inv) {
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'reference' => $data['reference'],
                'sold_at' => $data['sold_at'],
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $lineTotal = $row['quantity'] * $row['unit_price'];
                $item = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => $lineTotal,
                ]);
                $total += $lineTotal;

                $inv->stockOut($item->product, $item->quantity, 'sale', $sale->id, "Sale {$sale->reference}");
            }

            $sale->update(['total' => $total]);
            return new SaleResource($sale->load('items'));
        });
    }

    public function show(Sale $sale)
    {
        return new SaleResource($sale->load('items.product', 'customer'));
    }
}