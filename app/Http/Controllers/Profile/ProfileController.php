<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\DTO\Auth\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateAvatarRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Self-service account details and profile picture management.
 */
final class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.profile.edit', [
            'user' => $user,
            'timezones' => $this->timezones(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->profile->update($user, UpdateProfileData::fromRequest($request));

        return back()->with('status', __('profile.updated'));
    }

    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->profile->updateAvatar($user, $request->file('avatar'));

        return back()->with('status', __('profile.avatar_updated'));
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->profile->removeAvatar($user);

        return back()->with('status', __('profile.avatar_removed'));
    }

    /**
     * Timezone options offered by the preferences form.
     *
     * @return array<int, string>
     */
    private function timezones(): array
    {
        return \DateTimeZone::listIdentifiers();
    }
}
