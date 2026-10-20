<?php

namespace Database\Factories;

use App\Models\Consultant;
use App\Models\ConsultantImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsultantImage>
 */
class ConsultantImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consultant_id' => Consultant::factory(),
            'image_url' => 'images/scenes/meeting.png',
            'alt_text' => fake()->sentence(3),
            'tags' => [],
            'sort_order' => 0,
        ];
    }
}
