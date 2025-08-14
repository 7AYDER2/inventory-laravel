<?php

// routes/api.php
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class,'register']);
Route::post('/auth/login', [AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('customers', CustomerController::class);

    Route::post('purchases', [PurchaseController::class,'store']);  // create + stock-in
    Route::get('purchases/{purchase}', [PurchaseController::class,'show']);

    Route::post('sales', [SaleController::class,'store']);          // create + stock-out
    Route::get('sales/{sale}', [SaleController::class,'show']);

    Route::post('products/{product}/adjust', [ProductController::class,'adjust']); // manual adjust

    Route::get('reports/stock-value', [ReportController::class,'stockValue']);
    Route::get('reports/low-stock', [ReportController::class,'lowStock']);
    Route::get('reports/top-products', [ReportController::class,'topProducts']); // based on sales
});
