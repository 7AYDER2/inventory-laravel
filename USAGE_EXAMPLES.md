# Request and Resource Usage Examples

This document shows how to use the newly created request and resource classes in your Laravel controllers.

## AuthController

### Using Form Requests
```php
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        $token = $user->createToken('api')->plainTextToken;
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = \App\Models\User::where('email', $data['email'])->first();
        
        if (!$user || !\Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }
        
        $token = $user->createToken('api')->plainTextToken;
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }
}
```

## ProductController

### Using Form Requests
```php
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $product = Product::create($data);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);
        return new ProductResource($product);
    }

    public function adjust(AdjustStockRequest $request, Product $product, InventoryService $svc)
    {
        $data = $request->validated();
        $movement = $svc->adjust($product, $data['delta'], $data['note'] ?? null);
        
        return response()->json([
            'product' => new ProductResource($product->fresh()),
            'movement' => $movement
        ]);
    }
}
```

## CustomerController

### Using Form Requests
```php
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $customer = Customer::create($data);
        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();
        $customer->update($data);
        return new CustomerResource($customer);
    }
}
```

## SupplierController

### Using Form Requests
```php
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;

class SupplierController extends Controller
{
    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        $supplier = Supplier::create($data);
        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        $supplier->update($data);
        return new SupplierResource($supplier);
    }
}
```

## PurchaseController

### Using Form Requests
```php
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Resources\PurchaseResource;

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
```

## SaleController

### Using Form Requests
```php
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\SaleResource;

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
```

## ReportController

### Using Resources
```php
use App\Http\Resources\StockValueResource;
use App\Http\Resources\TopProductResource;
use App\Http\Resources\ProductResource;

class ReportController extends Controller
{
    public function stockValue()
    {
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
        $topProducts = \DB::table('sale_items')
            ->select('product_id', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(line_total) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();
            
        return TopProductResource::collection($topProducts);
    }
}
```

## Benefits of Using Form Requests and Resources

### Form Requests Benefits:
1. **Centralized Validation**: All validation rules are in one place
2. **Custom Error Messages**: Better user experience with custom error messages
3. **Authorization**: Can add authorization logic in the `authorize()` method
4. **Reusability**: Can be reused across multiple controllers
5. **Clean Controllers**: Controllers become cleaner and more focused

### Resource Benefits:
1. **Consistent API Responses**: Ensures consistent data structure
2. **Data Transformation**: Can transform data before sending to client
3. **Conditional Data**: Can include/exclude data based on conditions
4. **Nested Resources**: Can include related data when needed
5. **API Versioning**: Easy to maintain different API versions

## Collection Resources

For endpoints that return multiple items, use collection resources:

```php
// Instead of returning multiple resources manually
return ProductResource::collection($products);

// Or for paginated results
return ProductResource::collection($products->paginate(20));
```
