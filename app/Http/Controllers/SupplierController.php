<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::query()
            ->when(request('search'), fn($query) => $query->where('name', 'like', '%' . request('search') . '%')
                                             ->orWhere('email', 'like', '%' . request('search') . '%'))
            ->orderByDesc('id')
            ->paginate(20);
            
        return SupplierResource::collection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        $supplier = Supplier::create($data);
        return new SupplierResource($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        $supplier->update($data);
        return new SupplierResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->noContent();
    }
}
