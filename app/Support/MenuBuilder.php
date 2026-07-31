<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

/**
 * Builds the permission-aware sidebar navigation.
 *
 * Menus are grouped logically rather than presented as a long flat list
 * (11_Layout_System.md §19) and unauthorised entries are removed entirely.
 */
final class MenuBuilder
{
    /**
     * Sidebar groups rendered for the authenticated user.
     *
     * @return array<int, array{label: string, items: array<int, MenuItem>}>
     */
    public function sidebar(): array
    {
        $groups = [
            [
                'label' => __('navigation.groups.overview'),
                'items' => [
                    new MenuItem(
                        label: __('navigation.dashboard'),
                        route: 'dashboard',
                        icon: 'home',
                        permission: Permission::DashboardView->value,
                    ),
                ],
            ],
            [
                'label' => __('navigation.groups.account'),
                'items' => [
                    new MenuItem(
                        label: __('navigation.profile'),
                        route: 'profile.edit',
                        icon: 'user-circle',
                        permission: Permission::ProfileView->value,
                        activePattern: 'profile.*',
                    ),
                    new MenuItem(
                        label: __('navigation.security'),
                        route: 'profile.security',
                        icon: 'shield-check',
                        permission: Permission::SessionView->value,
                    ),
                ],
            ],
        ];

        return $this->filterGroups($groups, Auth::user());
    }

    /**
     * Quick actions surfaced on the dashboard.
     *
     * @return array<int, MenuItem>
     */
    public function quickActions(): array
    {
        $items = [
            new MenuItem(
                label: __('navigation.quick_actions.edit_profile'),
                route: 'profile.edit',
                icon: 'user-circle',
                permission: Permission::ProfileUpdate->value,
            ),
            new MenuItem(
                label: __('navigation.quick_actions.review_sessions'),
                route: 'profile.security',
                icon: 'shield-check',
                permission: Permission::SessionView->value,
            ),
            new MenuItem(
                label: __('navigation.quick_actions.login_history'),
                route: 'profile.login-history',
                icon: 'clock',
                permission: Permission::LoginHistoryView->value,
            ),
        ];

        return $this->filterItems($items, Auth::user());
    }

    /**
     * @param  array<int, array{label: string, items: array<int, MenuItem>}>  $groups
     * @return array<int, array{label: string, items: array<int, MenuItem>}>
     */
    private function filterGroups(array $groups, ?Authenticatable $user): array
    {
        $visible = [];

        foreach ($groups as $group) {
            $items = $this->filterItems($group['items'], $user);

            if ($items === []) {
                continue;
            }

            $visible[] = ['label' => $group['label'], 'items' => $items];
        }

        return $visible;
    }

    /**
     * @param  array<int, MenuItem>  $items
     * @return array<int, MenuItem>
     */
    private function filterItems(array $items, ?Authenticatable $user): array
    {
        return array_values(array_filter(
            $items,
            fn (MenuItem $item): bool => $this->isVisible($item, $user),
        ));
    }

    private function isVisible(MenuItem $item, ?Authenticatable $user): bool
    {
        if ($item->permission === null) {
            return true;
        }

        return $user !== null && $user->can($item->permission);
    }
}
