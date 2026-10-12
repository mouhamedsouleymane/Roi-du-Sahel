<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@roisdusahel.ne',
            'phone' => '90000001',
        ]);
        $this->admin->assignRole('Super Admin');
    }

    public function test_roles_index_page_can_be_rendered(): void
    {
        Role::create(['name' => 'Intendant', 'display_name' => 'Intendant Général']);

        $response = $this->actingAs($this->admin)->get(route('roles.index'));

        $response->assertStatus(200);
        $response->assertSee('Gestion des Rôles & Habilitations');
        $response->assertSee('Intendant Général');
    }

    public function test_new_role_can_be_created_with_permissions(): void
    {
        $perm1 = Permission::create([
            'name' => 'manage-canteen',
            'group' => 'administration',
            'display_name' => 'Gérer la cantine',
        ]);

        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Responsable Cantine',
            'display_name' => 'Responsable Cantine et Restauration',
            'description' => 'Supervise les repas et l\'approvisionnement.',
            'permissions' => [$perm1->id],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'Responsable Cantine',
            'display_name' => 'Responsable Cantine et Restauration',
        ]);

        $role = Role::firstWhere('name', 'Responsable Cantine');
        $this->assertNotNull($role);
        $this->assertTrue($role->permissions->contains('name', 'manage-canteen'));
    }

    public function test_role_can_be_updated_and_permissions_synced(): void
    {
        $perm1 = Permission::create(['name' => 'perm-one', 'group' => 'general']);
        $perm2 = Permission::create(['name' => 'perm-two', 'group' => 'general']);

        $role = Role::create([
            'name' => 'Comptable Adjoint',
            'display_name' => 'Comptable Adjoint',
        ]);
        $role->givePermissionTo($perm1);

        $response = $this->actingAs($this->admin)->patch(route('roles.update', $role), [
            'name' => 'Comptable Principal',
            'display_name' => 'Responsable Comptabilité',
            'description' => 'Gestion de la trésorerie.',
            'permissions' => [$perm2->id],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role->refresh();
        $this->assertEquals('Comptable Principal', $role->name);
        $this->assertEquals('Responsable Comptabilité', $role->display_name);
        $this->assertTrue($role->permissions->contains('name', 'perm-two'));
        $this->assertFalse($role->permissions->contains('name', 'perm-one'));
    }

    public function test_protected_role_cannot_be_deleted(): void
    {
        $superAdminRole = Role::firstWhere('name', 'Super Admin');
        if (! $superAdminRole) {
            $superAdminRole = Role::create(['name' => 'Super Admin', 'display_name' => 'Super Admin']);
        }

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $superAdminRole));

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Custom Role', 'display_name' => 'Custom Role']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $role));

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_unassigned_custom_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Temporary Role', 'display_name' => 'Temporary Role']);

        $response = $this->actingAs($this->admin)->delete(route('roles.destroy', $role));

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
