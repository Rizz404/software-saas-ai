<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Modules\Consultation\Enums\ConsultationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => Consultation::generateReference(),
            'company_id' => Company::factory(),
            'consultant_id' => fn (array $attributes) => Consultant::factory()->create(['company_id' => $attributes['company_id']])->id,
            'client_name' => fake()->name(),
            'client_phone' => '+62 811 1111 222',
            'contact_type' => 'phone',
            'consultation_date' => now()->addDays(3)->toDateString(),
            'time_slot' => '11:00',
            'participants' => 2,
            'consultation_fee_total' => 0,
            'consultant_name_snapshot' => 'Demo Consultant',
            'currency_snapshot' => 'IDR',
            'duration_minutes' => 60,
            'status' => ConsultationStatus::Pending->value,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => ConsultationStatus::Confirmed->value]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ConsultationStatus::Cancelled->value]);
    }
}
