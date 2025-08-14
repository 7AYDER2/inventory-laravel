<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['customer_id','reference','sold_at','total'];
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function items(){ return $this->hasMany(SaleItem::class); }
}
