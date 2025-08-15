<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\InventoryServices;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::query()
            ->when(request('search'), fn($query) => $query->where('name', 'like', '%' . request('search') . '%')
                                             ->orWhere('sku', 'like', '%' . request('search') . '%'))
            ->orderByDesc('id')
            ->paginate(20);
            
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $product = Product::create($data);
        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }

    public function adjust(AdjustStockRequest $request, Product $product, InventoryServices $svc)
    {
        $data = $request->validated();
        $movement = $svc->adjust($product, $data['delta'], $data['note'] ?? null);
        
        return response()->json([
            'product' => new ProductResource($product->fresh()),
            'movement' => $movement
        ]);
    }
}
