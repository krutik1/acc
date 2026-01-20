<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
        return [
            'party_id' => ['required', 'exists:parties,id'],
            'invoice_date' => ['required', 'date'],
            'challan_ids' => ['required', 'array', 'min:1'],
            'challan_ids.*' => ['required', 'exists:challans,id'],
            'gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tds_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.date' => 'Please enter a valid date.',
            'challan_ids.required' => 'Please select at least one challan.',
            'challan_ids.min' => 'Please select at least one challan.',
            'challan_ids.*.exists' => 'One or more selected challans are invalid.',
            'gst_percent.numeric' => 'GST percentage must be a number.',
            'gst_percent.min' => 'GST percentage cannot be negative.',
            'gst_percent.max' => 'GST percentage cannot exceed 100%.',
            'tds_percent.numeric' => 'TDS percentage must be a number.',
            'tds_percent.min' => 'TDS percentage cannot be negative.',
            'tds_percent.max' => 'TDS percentage cannot exceed 100%.',
            'discount_type.required' => 'Discount type is required.',
            'discount_type.in' => 'Invalid discount type.',
            'discount_value.numeric' => 'Discount value must be a number.',
            'discount_value.min' => 'Discount value cannot be negative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults for empty numeric fields
        $this->merge([
            'gst_percent' => $this->gst_percent ?? 0,
            'tds_percent' => $this->tds_percent ?? 0,
            'discount_value' => $this->discount_value ?? 0,
            'discount_type' => $this->discount_type ?? 'fixed',
        ]);
    }
}
