<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
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
