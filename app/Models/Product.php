<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','sku','category','cost_price','selling_price','quantity_in_stock','min_stock'
    ];

    public function stockMovements() { return $this->hasMany(StockMovement::class);}
}
