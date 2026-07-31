<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates self-service profile changes.
 */
final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email:filter',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->user()?->getKey())
                    ->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'timezone' => ['nullable', 'string', 'timezone:all'],
            'locale' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => __('auth.fields.first_name'),
            'last_name' => __('auth.fields.last_name'),
            'email' => __('auth.fields.email'),
            'phone' => __('auth.fields.phone'),
            'timezone' => __('profile.fields.timezone'),
            'locale' => __('profile.fields.locale'),
        ];
    }
}
