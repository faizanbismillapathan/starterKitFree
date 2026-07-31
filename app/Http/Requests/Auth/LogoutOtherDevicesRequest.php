<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the confirmation required before revoking other sessions.
 */
final class LogoutOtherDevicesRequest extends FormRequest
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
            'password' => ['required', 'string', 'current_password:web'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['password.current_password' => __('profile.errors.current_password')];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['password' => __('auth.fields.password')];
    }
}
