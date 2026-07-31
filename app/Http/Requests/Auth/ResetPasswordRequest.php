<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the password reset submission.
 */
final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'password' => ['required', 'string', 'confirmed', StrongPassword::rules()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => __('auth.fields.email'),
            'password' => __('auth.fields.password'),
        ];
    }
}
