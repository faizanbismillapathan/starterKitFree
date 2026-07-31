<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ThemeMode;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

/**
 * Persists the light/dark/system preference for guests and members alike.
 */
final class ThemeController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in(ThemeMode::values())],
        ]);

        $mode = ThemeMode::from($validated['theme']);
        $user = $request->user();

        if ($user instanceof User) {
            $this->profile->updateTheme($user, $mode);
        }

        Cookie::queue(
            (string) config('theme.cookie'),
            $mode->value,
            (int) config('theme.cookie_lifetime'),
        );

        return back();
    }
}
