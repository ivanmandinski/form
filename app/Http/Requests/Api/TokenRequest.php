<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TokenRequest extends FormRequest
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
            'token_name' => ['nullable', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:255'],
            'revoke_existing' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token_name.max' => 'The token name must not exceed 255 characters.',
            'abilities.array' => 'The abilities must be an array.',
            'abilities.*.string' => 'Each ability must be a string.',
            'abilities.*.max' => 'Each ability must not exceed 255 characters.',
        ];
    }
}
