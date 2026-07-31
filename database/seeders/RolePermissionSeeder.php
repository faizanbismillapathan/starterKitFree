<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\RoleType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Registers the roles and permissions shipped with the Free Edition.
 *
 * The seeder is idempotent so it may run safely on every deployment.
 */
final class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions granted to each role.
     *
     * @var array<string, array<int, PermissionEnum>>
     */
    private const ROLE_PERMISSIONS = [
        RoleType::Manager->value => [
            PermissionEnum::DashboardView,
            PermissionEnum::DashboardCustomize,
            PermissionEnum::DashboardViewSystemStatistics,
            PermissionEnum::ProfileView,
            PermissionEnum::ProfileUpdate,
            PermissionEnum::SessionView,
            PermissionEnum::SessionRevoke,
            PermissionEnum::LoginHistoryView,
            PermissionEnum::MediaView,
            PermissionEnum::MediaUpload,
            PermissionEnum::MediaDelete,
        ],
        RoleType::Viewer->value => [
            PermissionEnum::DashboardView,
            PermissionEnum::ProfileView,
            PermissionEnum::ProfileUpdate,
            PermissionEnum::SessionView,
            PermissionEnum::SessionRevoke,
            PermissionEnum::LoginHistoryView,
            PermissionEnum::MediaView,
            PermissionEnum::MediaUpload,
            PermissionEnum::MediaDelete,
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createPermissions(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }
    }

    private function createRoles(): void
    {
        foreach (RoleType::cases() as $roleType) {
            $role = Role::findOrCreate($roleType->value, 'web');

            $role->syncPermissions($this->permissionsFor($roleType));
        }
    }

    /**
     * Super Administrator and Administrator receive every permission so new
     * capabilities are available without editing the seeder.
     *
     * @return array<int, string>
     */
    private function permissionsFor(RoleType $role): array
    {
        return match ($role) {
            RoleType::SuperAdministrator,
            RoleType::Administrator => PermissionEnum::values(),
            default => array_map(
                static fn (PermissionEnum $permission): string => $permission->value,
                self::ROLE_PERMISSIONS[$role->value] ?? [],
            ),
        };
    }
}
