<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a new account submission.
 */
final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('starter_kit.auth.registration_enabled');
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
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'confirmed', StrongPassword::rules()],
            'terms' => ['accepted'],
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
            'password' => __('auth.fields.password'),
            'terms' => __('auth.fields.terms'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'email' => $this->has('email') ? trim((string) $this->input('email')) : null,
            'first_name' => $this->has('first_name') ? trim((string) $this->input('first_name')) : null,
            'last_name' => $this->has('last_name') ? trim((string) $this->input('last_name')) : null,
        ], static fn (?string $value): bool => $value !== null));
    }
}
