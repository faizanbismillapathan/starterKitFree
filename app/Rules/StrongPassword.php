<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

/**
 * Builds the configured password policy.
 *
 * Requirements are configurable rather than hardcoded
 * (17_Authentication_Module.md §16).
 */
final class StrongPassword
{
    public static function rules(): Password
    {
        $policy = (array) config('starter_kit.auth.password');

        $password = Password::min((int) ($policy['min_length'] ?? 10));

        // Laravel expresses both case requirements through a single rule.
        if (($policy['require_uppercase'] ?? true) || ($policy['require_lowercase'] ?? true)) {
            $password = $password->mixedCase();
        }

        if ($policy['require_numbers'] ?? true) {
            $password = $password->numbers();
        }

        if ($policy['require_symbols'] ?? true) {
            $password = $password->symbols();
        }

        return $password->uncompromised();
    }

    /**
     * Human readable checklist rendered beside password inputs.
     *
     * @return array<int, string>
     */
    public static function describe(): array
    {
        $policy = (array) config('starter_kit.auth.password');

        $requirements = [
            __('auth.password_policy.min_length', ['count' => (int) ($policy['min_length'] ?? 10)]),
        ];

        if (($policy['require_uppercase'] ?? true) && ($policy['require_lowercase'] ?? true)) {
            $requirements[] = __('auth.password_policy.mixed_case');
        } elseif ($policy['require_uppercase'] ?? true) {
            $requirements[] = __('auth.password_policy.uppercase');
        } elseif ($policy['require_lowercase'] ?? true) {
            $requirements[] = __('auth.password_policy.lowercase');
        }

        if ($policy['require_numbers'] ?? true) {
            $requirements[] = __('auth.password_policy.numbers');
        }

        if ($policy['require_symbols'] ?? true) {
            $requirements[] = __('auth.password_policy.symbols');
        }

        $requirements[] = __('auth.password_policy.uncompromised');

        return $requirements;
    }
}
