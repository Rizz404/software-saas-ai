<?php

namespace Database\Factories;

use App\Models\Consultant;
use App\Models\ConsultationSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsultationSlot>
 */
class ConsultationSlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consultant_id' => Consultant::factory(),
            'consultation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'total_sessions' => 1,
            'reserved_sessions' => 0,
        ];
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => ['reserved_sessions' => $attributes['total_sessions']]);
    }
}
