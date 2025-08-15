<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'supplier_id' => 'required|exists:suppliers,id',
            'reference' => 'required|string|unique:purchases,reference',
            'purchased_at' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'A supplier must be selected.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'reference.required' => 'A purchase reference is required.',
            'reference.unique' => 'This purchase reference is already in use.',
            'purchased_at.required' => 'The purchase date is required.',
            'purchased_at.date' => 'Please enter a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'A product must be selected.',
            'items.*.product_id.exists' => 'The selected product is invalid.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.integer' => 'Quantity must be a whole number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_cost.required' => 'Unit cost is required.',
            'items.*.unit_cost.numeric' => 'Unit cost must be a number.',
            'items.*.unit_cost.min' => 'Unit cost cannot be negative.',
        ];
    }
}
