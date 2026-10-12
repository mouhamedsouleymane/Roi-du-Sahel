<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardians_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Guardian::create([
            'first_name' => 'Moussa',
            'last_name' => 'Souley',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 11 22 33',
        ]);

        $response = $this->actingAs($user)->get(route('guardians.index'));

        $response->assertStatus(200);
        $response->assertSee('Moussa');
        $response->assertSee('+227 90 11 22 33');
    }

    public function test_guardians_can_be_filtered_by_search_and_relationship(): void
    {
        $user = User::factory()->create();

        Guardian::create([
            'first_name' => 'Amina',
            'last_name' => 'Ibrahim',
            'relationship' => 'MERE',
            'phone_primary' => '+227 91 00 00 01',
        ]);

        Guardian::create([
            'first_name' => 'Ousmane',
            'last_name' => 'Abdou',
            'relationship' => 'PERE',
            'phone_primary' => '+227 92 00 00 02',
        ]);

        $response = $this->actingAs($user)->get(route('guardians.index', ['search' => 'Amina', 'relationship' => 'MERE']));

        $response->assertStatus(200);
        $response->assertSee('Amina');
        $response->assertDontSee('Ousmane');
    }

    public function test_guardian_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('guardians.create'));

        $response->assertStatus(200);
        $response->assertSee('Nouveau Parent / Tuteur Légal');
    }

    public function test_guardian_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('guardians.store'), [
            'first_name' => 'Fatima',
            'last_name' => 'Zarma',
            'relationship' => 'MERE',
            'phone_primary' => '+227 98 76 54 32',
            'phone_secondary' => '+227 89 76 54 32',
            'email' => 'fatima.zarma@example.com',
            'profession' => 'Médecin',
            'city' => 'Niamey',
            'address' => 'Plateau 1',
        ]);

        $guardian = Guardian::where('phone_primary', '+227 98 76 54 32')->first();
        $this->assertNotNull($guardian);
        $this->assertSame('Fatima', $guardian->first_name);

        $response->assertRedirect(route('guardians.show', $guardian));
    }

    public function test_guardian_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $guardian = Guardian::create([
            'first_name' => 'Boubacar',
            'last_name' => 'Hassane',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 99 88 77',
            'city' => 'Niamey',
        ]);

        $response = $this->actingAs($user)->get(route('guardians.show', $guardian));

        $response->assertStatus(200);
        $response->assertSee('Boubacar');
        $response->assertSee('Hassane');
    }

    public function test_guardian_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $guardian = Guardian::create([
            'first_name' => 'Boubacar',
            'last_name' => 'Hassane',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 99 88 77',
        ]);

        $response = $this->actingAs($user)->get(route('guardians.edit', $guardian));

        $response->assertStatus(200);
        $response->assertSee('Modifier le Tuteur');
    }

    public function test_guardian_can_be_updated(): void
    {
        $user = User::factory()->create();

        $guardian = Guardian::create([
            'first_name' => 'Boubacar',
            'last_name' => 'Hassane',
            'relationship' => 'PERE',
            'phone_primary' => '+227 90 99 88 77',
        ]);

        $response = $this->actingAs($user)->put(route('guardians.update', $guardian), [
            'first_name' => 'Boubacar Updated',
            'last_name' => 'Hassane',
            'relationship' => 'TUTEUR_LEGAL',
            'phone_primary' => '+227 90 99 88 00',
            'phone_secondary' => null,
            'email' => null,
            'profession' => null,
            'city' => 'Niamey',
            'address' => null,
        ]);

        $response->assertRedirect(route('guardians.show', $guardian));

        $guardian->refresh();
        $this->assertSame('Boubacar Updated', $guardian->first_name);
        $this->assertSame('TUTEUR_LEGAL', $guardian->relationship);
        $this->assertSame('+227 90 99 88 00', $guardian->phone_primary);
    }
}
