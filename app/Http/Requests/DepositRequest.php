<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can make deposits — role check is handled by middleware,
        // but we add an extra guard here.
        return $this->user() && in_array($this->user()->role, ['admin', 'super_admin'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user for the deposit.',
            'user_id.exists' => 'The selected user does not exist.',
            'amount.required' => 'Please enter the deposit amount.',
            'amount.min' => 'The minimum deposit amount is 1 BDT.',
            'amount.max' => 'The maximum deposit amount is 99,999,999 BDT.',
            'note.max' => 'The note must not exceed 500 characters.',
        ];
    }
}
