<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'gst_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9A-Z]{15}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Party name is required.',
            'address.required' => 'Address is required.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Please enter a valid contact number.',
            'gst_number.regex' => 'GST number must be a valid 15-character alphanumeric code.',
        ];
    }
}
