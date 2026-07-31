<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a password change initiated by the account owner.
 *
 * The current password is always re-confirmed before sensitive changes
 * (19_Profile_Module.md §24).
 */
final class UpdatePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', StrongPassword::rules()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => __('profile.errors.current_password'),
            'password.different' => __('profile.errors.password_reuse'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => __('profile.fields.current_password'),
            'password' => __('profile.fields.new_password'),
        ];
    }
}
