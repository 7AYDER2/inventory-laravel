<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::query()
            ->when(request('search'), fn($search) => $search->where('name','like','%'.request('search').'%')
                                             ->orWhere('sku','like','%'.request('search').'%'))
            ->orderByDesc('id')
            ->paginate(20);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'sku'=>'required|string|max:100|unique:products,sku',
            'category'=>'nullable|string|max:100',
            'cost_price'=>'required|numeric|min:0',
            'selling_price'=>'required|numeric|min:0',
            'quantity_in_stock'=>'integer|min:0',
            'min_stock'=>'integer|min:0',
        ]);

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
      public function show(Product $product) { return $product; }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'=>'sometimes|required|string|max:255',
            'sku'=>"sometimes|required|string|max:100|unique:products,sku,{$product->id}",
            'category'=>'nullable|string|max:100',
            'cost_price'=>'sometimes|required|numeric|min:0',
            'selling_price'=>'sometimes|required|numeric|min:0',
            'min_stock'=>'sometimes|integer|min:0',
        ]);

        $product->update($data);
        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }

    public function adjust(Request $request, Product $product, InventoryService $svc)
    {
        $data = $request->validate([
            'delta' => 'required|integer',
            'note'  => 'nullable|string'
        ]);

        $movement = $svc->adjust($product, $data['delta'], $data['note'] ?? null);
        return response()->json(['product'=>$product->fresh(),'movement'=>$movement]);
    }
}
