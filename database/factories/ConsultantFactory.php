<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Consultant;
use App\Modules\Consultants\Enums\ConsultantSpecialty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Consultant>
 */
class ConsultantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'company_id' => Company::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'specialty' => ConsultantSpecialty::Solution->value,
            'min_participants' => 1,
            'max_participants' => 6,
            'features' => [],
            'consultation_fee' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forGuests(int $min, int $max): static
    {
        return $this->state(fn () => ['min_participants' => $min, 'max_participants' => $max]);
    }

    public function withFee(float $fee): static
    {
        return $this->state(fn () => ['consultation_fee' => $fee]);
    }
}
