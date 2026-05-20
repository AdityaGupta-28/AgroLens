<?php

namespace App\Traits;

use App\Enums\Permission as PermissionEnum;
use App\Enums\UserRole;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    public function hasPermission(PermissionEnum|string $permission): bool
    {
        $name = $permission instanceof PermissionEnum ? $permission->value : $permission;

        $role = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        return Cache::remember(
            "user.{$this->id}.permission.{$name}",
            now()->addMinutes(10),
            fn () => Permission::roleHas($role, $name)
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isGovernmentOfficer(): bool
    {
        return $this->role === UserRole::GovernmentOfficer;
    }

    public function isPublicViewer(): bool
    {
        return $this->role === UserRole::PublicViewer;
    }

    public function clearPermissionCache(): void
    {
        foreach (PermissionEnum::cases() as $case) {
            Cache::forget("user.{$this->id}.permission.{$case->value}");
        }
    }
}
