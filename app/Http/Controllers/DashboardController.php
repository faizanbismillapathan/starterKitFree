<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use App\Support\MenuBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Presents the authenticated landing page (18_Dashboard_Module.md).
 */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly MenuBuilder $menu,
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.dashboard.index', [
            'statistics' => $this->dashboard->statistics($user),
            'registrationTrend' => $this->dashboard->registrationTrend(),
            'authenticationSummary' => $this->dashboard->authenticationSummary(),
            'recentActivity' => $this->dashboard->recentActivity($user),
            'recentUsers' => $this->dashboard->recentUsers($user),
            'systemStatus' => $this->dashboard->systemStatus($user),
            'quickActions' => $this->menu->quickActions(),
        ]);
    }
}
