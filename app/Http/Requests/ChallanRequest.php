<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallanRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $challanId = $this->route('challan') ? $this->route('challan')->id : null;
        
        return [
            'party_id' => ['required', 'exists:parties,id'],
            'challan_number' => [
                'nullable',
                'string',
                'max:50',
                'unique:challans,challan_number,' . $challanId,
            ],
            'challan_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001', 'max:999999.999'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'party_id.required' => 'Please select a party.',
            'party_id.exists' => 'Selected party is invalid.',
            'challan_number.unique' => 'This challan number already exists.',
            'challan_date.required' => 'Challan date is required.',
            'challan_date.date' => 'Please enter a valid date.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.quantity.required' => 'Quantity is required.',
            'items.*.quantity.min' => 'Quantity must be greater than 0.',
            'items.*.unit.required' => 'Unit is required.',
            'items.*.rate.required' => 'Rate is required.',
            'items.*.rate.min' => 'Rate must be 0 or greater.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Filter out empty item rows
        if ($this->has('items')) {
            $items = collect($this->items)->filter(function ($item) {
                return !empty($item['description']) || !empty($item['quantity']) || !empty($item['rate']);
            })->values()->all();
            
            $this->merge(['items' => $items]);
        }
    }
}
