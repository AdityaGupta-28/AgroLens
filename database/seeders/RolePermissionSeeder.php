<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\UserRole;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role_permissions')->delete();
        Permission::query()->delete();

        $permissions = collect(PermissionEnum::cases())->map(fn ($p) => Permission::create([
            'name' => $p->value,
            'label' => $p->label(),
        ]));

        $officerPermissions = [
            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewGis,
            PermissionEnum::ManageRegions,
            PermissionEnum::ManageFarmers,
            PermissionEnum::ManageSurveys,
            PermissionEnum::CollectSurveyData,
            PermissionEnum::ViewApi,
        ];

        $viewerPermissions = [
            PermissionEnum::ViewDashboard,
            PermissionEnum::ViewGis,
        ];

        foreach (PermissionEnum::cases() as $perm) {
            DB::table('role_permissions')->insert([
                'role' => UserRole::SuperAdmin->value,
                'permission_id' => $permissions->firstWhere('name', $perm->value)->id,
            ]);
        }

        foreach ($officerPermissions as $perm) {
            DB::table('role_permissions')->insert([
                'role' => UserRole::GovernmentOfficer->value,
                'permission_id' => $permissions->firstWhere('name', $perm->value)->id,
            ]);
        }

        foreach ($viewerPermissions as $perm) {
            DB::table('role_permissions')->insert([
                'role' => UserRole::PublicViewer->value,
                'permission_id' => $permissions->firstWhere('name', $perm->value)->id,
            ]);
        }
    }
}
