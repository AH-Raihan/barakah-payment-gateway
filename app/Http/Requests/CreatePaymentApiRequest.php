<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentApiRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:500'],
            'callback_url' => ['nullable', 'url', 'max:500'],
            'expires_in_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'The minimum payment amount is 1 BDT.',
            'description.required' => 'A payment description is required.',
            'description.max' => 'The description must not exceed 500 characters.',
            'callback_url.url' => 'The callback URL must be a valid URL.',
            'callback_url.max' => 'The callback URL must not exceed 500 characters.',
            'expires_in_minutes.min' => 'The payment must be valid for at least 5 minutes.',
            'expires_in_minutes.max' => 'The payment cannot be valid for more than 24 hours (1440 minutes).',
        ];
    }
}
