<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'category' => $this->category,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'quantity_in_stock' => $this->quantity_in_stock,
            'min_stock' => $this->min_stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
