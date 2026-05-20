<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    protected $fillable = ['name', 'label'];

    public static function roleHas(string|UserRole $role, string $permissionName): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role', $roleValue)
            ->where('permissions.name', $permissionName)
            ->exists();
    }
}
