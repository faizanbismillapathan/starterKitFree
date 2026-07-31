<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginHistoryResource;
use App\Http\Resources\StatisticCardResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\DashboardService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only dashboard aggregates for API clients.
 */
final class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ResponseBuilder::success([
            'statistics' => StatisticCardResource::collection(
                $this->dashboard->statistics($user),
            )->resolve(),
            'authentication_summary' => $this->dashboard->authenticationSummary(),
            'recent_activity' => LoginHistoryResource::collection(
                $this->dashboard->recentActivity($user),
            )->resolve(),
            'recent_users' => UserResource::collection(
                $this->dashboard->recentUsers($user),
            )->resolve(),
        ], __('dashboard.api.retrieved'));
    }

    public function statistics(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ResponseBuilder::success(
            StatisticCardResource::collection($this->dashboard->statistics($user))->resolve(),
            __('dashboard.api.statistics_retrieved'),
        );
    }

    public function charts(Request $request): JsonResponse
    {
        return ResponseBuilder::success([
            'registration_trend' => $this->dashboard->registrationTrend(),
            'authentication_summary' => $this->dashboard->authenticationSummary(),
        ], __('dashboard.api.charts_retrieved'));
    }
}
