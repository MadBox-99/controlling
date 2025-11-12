<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $apiKey = $this->bearerToken();
        $expectedApiKey = config('services.secondary_app.api_key');

        // Validate API key format (8-8-8-8 hexadecimal format)
        if ($apiKey && ! preg_match('/^[a-f0-9]{8}-[a-f0-9]{8}-[a-f0-9]{8}-[a-f0-9]{8}$/', $apiKey)) {
            return false;
        }

        return $apiKey && $expectedApiKey && $apiKey === $expectedApiKey;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password_hash' => ['required', 'string', 'min:60'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be valid.',
            'email.exists' => 'User not found with this email address.',
            'password_hash.required' => 'Password hash is required.',
            'password_hash.string' => 'Password hash must be a string.',
            'password_hash.min' => 'Password hash must be at least 60 characters.',
        ];
    }
}
