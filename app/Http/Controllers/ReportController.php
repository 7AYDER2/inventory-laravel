<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockValueResource;
use App\Http\Resources\TopProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function stockValue()
    {
        // Sum of quantity_in_stock * cost_price and selling_price for potential value
        $valueAtCost = Product::sum(DB::raw('quantity_in_stock * cost_price'));
        $valueAtSell = Product::sum(DB::raw('quantity_in_stock * selling_price'));
        
        return new StockValueResource(compact('valueAtCost', 'valueAtSell'));
    }

    public function lowStock()
    {
        $products = Product::whereColumn('quantity_in_stock', '<=', 'min_stock')
            ->orderBy('quantity_in_stock')
            ->get();
            
        return ProductResource::collection($products);
    }

    public function topProducts()
    {
        $topProducts = DB::table('sale_items')
            ->select('product_id', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(line_total) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();
            
        return TopProductResource::collection($topProducts);
    }
}
