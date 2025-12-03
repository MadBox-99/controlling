<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class UserSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by API key middleware
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'new_email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string'], // Raw password - will be hashed in controller
            'role' => ['nullable', 'string'],
        ];
    }
}
