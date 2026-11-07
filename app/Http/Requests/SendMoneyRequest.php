<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMoneyRequest extends FormRequest
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
            'recipient' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999'],
            'description' => ['nullable', 'string', 'max:255'],
            'pin' => ['required', 'string', 'size:5'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'recipient.required' => 'Please enter a recipient (phone, email, or account ID).',
            'amount.required' => 'Please enter the amount to send.',
            'amount.min' => 'The minimum transfer amount is 1 BDT.',
            'amount.max' => 'The maximum transfer amount is 999,999 BDT.',
            'pin.required' => 'Please enter your PIN to confirm.',
            'pin.size' => 'PIN must be exactly 5 digits.',
            'idempotency_key.required' => 'A unique transaction key is required.',
        ];
    }
}
