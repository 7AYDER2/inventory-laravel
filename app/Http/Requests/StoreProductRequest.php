<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'category' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'integer|min:0',
            'min_stock' => 'integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'cost_price.required' => 'The cost price is required.',
            'cost_price.numeric' => 'The cost price must be a number.',
            'cost_price.min' => 'The cost price cannot be negative.',
            'selling_price.required' => 'The selling price is required.',
            'selling_price.numeric' => 'The selling price must be a number.',
            'selling_price.min' => 'The selling price cannot be negative.',
        ];
    }
}
