<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_users_index_page_can_be_rendered(): void
    {
        Role::create(['name' => 'Directeur', 'display_name' => 'Directeur']);

        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Gestion des Utilisateurs');
    }

    public function test_users_can_be_filtered_by_search_or_role(): void
    {
        $teacherRole = Role::create(['name' => 'Enseignant', 'display_name' => 'Enseignant']);
        $parentRole = Role::create(['name' => 'Parent', 'display_name' => 'Parent']);

        $user1 = User::factory()->create(['name' => 'Moussa Boubacar', 'email' => 'moussa@test.com', 'phone' => '91112233']);
        $user1->assignRole($teacherRole);

        $user2 = User::factory()->create(['name' => 'Fatima Ibrahim', 'email' => 'fatima@test.com', 'phone' => '92223344']);
        $user2->assignRole($parentRole);

        // Search by phone
        $responsePhone = $this->actingAs($this->admin)->get(route('users.index', ['search' => '91112233']));
        $responsePhone->assertStatus(200);
        $responsePhone->assertSee('Moussa Boubacar');
        $responsePhone->assertDontSee('Fatima Ibrahim');

        // Filter by role
        $responseRole = $this->actingAs($this->admin)->get(route('users.index', ['role' => 'Parent']));
        $responseRole->assertStatus(200);
        $responseRole->assertSee('Fatima Ibrahim');
        $responseRole->assertDontSee('Moussa Boubacar');
    }

    public function test_new_user_can_be_created_with_role(): void
    {
        Role::create(['name' => 'Directeur', 'display_name' => 'Directeur']);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Ali Abdou',
            'email' => 'ali@roisdusahel.ne',
            'phone' => '93334455',
            'password' => 'password123',
            'role' => 'Directeur',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Ali Abdou',
            'email' => 'ali@roisdusahel.ne',
            'phone' => '93334455',
        ]);

        $createdUser = User::where('email', 'ali@roisdusahel.ne')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Directeur'));
    }

    public function test_user_can_be_updated(): void
    {
        $role1 = Role::create(['name' => 'Enseignant', 'display_name' => 'Enseignant']);
        $role2 = Role::create(['name' => 'Censeur', 'display_name' => 'Censeur']);

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@test.com',
            'phone' => '94445566',
        ]);
        $user->assignRole($role1);

        $response = $this->actingAs($this->admin)->patch(route('users.update', $user), [
            'name' => 'New Name',
            'email' => 'new@test.com',
            'phone' => '95556677',
            'role' => 'Censeur',
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@test.com', $user->email);
        $this->assertEquals('95556677', $user->phone);
        $this->assertTrue($user->hasRole('Censeur'));
        $this->assertFalse($user->hasRole('Enseignant'));
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_authenticated_user_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
