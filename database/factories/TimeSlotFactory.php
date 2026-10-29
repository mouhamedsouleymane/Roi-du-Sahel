<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hour = fake()->unique(true)->numberBetween(7, 17);
        $start = sprintf('%02d:00', $hour);
        $end = sprintf('%02d:00', $hour + 1);

        return [
            'name' => "{$hour}h - " . ($hour + 1) . 'h',
            'label' => "{$start} - {$end}",
            'start_time' => "{$start}:00",
            'end_time' => "{$end}:00",
            'cycle_id' => null,
            'academic_year_id' => null,
            'order_index' => $hour,
            'is_pause' => false,
            'is_active' => true,
            'color' => null,
        ];
    }
}

