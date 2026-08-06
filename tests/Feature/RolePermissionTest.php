<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_can_be_assigned_to_user(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'Directeur',
            'display_name' => 'Directeur Général',
        ]);

        $permission = Permission::create([
            'name' => 'manage-settings',
            'display_name' => 'Gérer les Paramètres',
        ]);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Directeur'));
        $this->assertTrue($user->hasPermissionTo('manage-settings'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasPermissionTo('any-random-permission'));
    }
}
