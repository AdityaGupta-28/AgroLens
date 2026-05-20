<?php

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('government officer can access dashboard', function () {
    $user = User::factory()->create([
        'role' => UserRole::GovernmentOfficer,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('public viewer cannot access admin panel', function () {
    $user = User::factory()->create([
        'role' => UserRole::PublicViewer,
        'email_verified_at' => now(),
    ]);

    expect($user->hasPermission(Permission::ManageUsers))->toBeFalse();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});
