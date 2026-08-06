<?php

namespace Tests\Feature;

use App\Models\ClassSubjectCoefficient;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectAndCoefficientTest extends TestCase
{
    use RefreshDatabase;

    public function test_subjects_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Subject::create([
            'code' => 'MATH',
            'name' => 'Mathématiques',
            'category' => 'scientifique',
        ]);

        $response = $this->actingAs($user)->get('/subjects');

        $response->assertStatus(200);
        $response->assertSee('Mathématiques');
    }

    public function test_subject_and_coefficient_can_be_created(): void
    {
        $user = User::factory()->create();

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '3EME', 'name' => 'Troisième']);

        $subject = Subject::create([
            'code' => 'PC',
            'name' => 'Physique-Chimie',
            'category' => 'scientifique',
        ]);

        $response = $this->actingAs($user)->post('/subjects/coefficients', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
            'weekly_hours' => 4,
        ]);

        $response->assertRedirect('/subjects');
        $this->assertDatabaseHas('class_subject_coefficients', [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'coefficient' => 3,
        ]);
    }
}
