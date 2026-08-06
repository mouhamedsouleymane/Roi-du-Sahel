<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        SchoolSetting::set('school_name', 'Complexe Scolaire Privé Les Rois du Sahel');

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Complexe Scolaire Privé Les Rois du Sahel');
    }

    public function test_settings_can_be_updated(): void
    {
        $user = User::factory()->create();

        SchoolSetting::set('school_name', 'Old Name');

        $response = $this->actingAs($user)->post('/settings', [
            'settings' => [
                'school_name' => 'Complexe Scolaire Privé Les Rois du Sahel',
            ],
        ]);

        $response->assertRedirect('/settings');
        $this->assertEquals('Complexe Scolaire Privé Les Rois du Sahel', SchoolSetting::get('school_name'));
    }
}
