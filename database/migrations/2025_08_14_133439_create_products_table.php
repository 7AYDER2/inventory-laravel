<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();// the code of the product
            $table->string('category')->nullable();
            $table->decimal('cost_price', 12, 2)->default(0); // 10,0 number of digits and 2 decimal places
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
