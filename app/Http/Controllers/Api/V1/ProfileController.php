<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\LoginHistoryRepositoryInterface;
use App\DTO\Auth\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\LoginHistoryResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ProfileService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Profile endpoints exposed to authenticated API clients.
 */
final class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profile,
        private readonly LoginHistoryRepositoryInterface $histories,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return ResponseBuilder::success(
            new UserResource($request->user()->load('roles')),
            __('profile.api.retrieved'),
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updated = $this->profile->update($user, UpdateProfileData::fromRequest($request));

        return ResponseBuilder::success(
            new UserResource($updated->load('roles')),
            __('profile.api.updated'),
        );
    }

    public function loginHistory(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $perPage = $this->resolvePerPage($request);

        return ResponseBuilder::success(
            LoginHistoryResource::collection(
                $this->histories->paginateForUser($user, $perPage),
            ),
            __('profile.api.login_history_retrieved'),
        );
    }

    private function resolvePerPage(Request $request): int
    {
        $options = (array) config('starter_kit.pagination.options');
        $requested = (int) $request->integer('per_page');

        return in_array($requested, $options, true)
            ? $requested
            : (int) config('starter_kit.pagination.default');
    }
}
