<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\Product;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Validation\ConsultationRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Turns the guest's already well-formed request into the consultant and
 * date it names, or tells them in their own language what does not fit.
 */
class ResolveConsultationTarget
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: Consultant, 1: CarbonImmutable, 2: ?Product}
     *
     * @throws ValidationException
     */
    public function handle(Company $company, array $data, string $locale): array
    {
        $area = $company->consultants()->where('is_active', true)->where('slug', $data['consultant_slug'])->first();

        if (! $area) {
            throw ValidationException::withMessages(['consultant_slug' => ConsultationRules::message('area_unknown', $locale)]);
        }

        $date = CarbonImmutable::parse($data['consultation_date'], $company->timezone)->startOfDay();
        $today = CarbonImmutable::now($company->timezone)->startOfDay();

        if ($today->diffInDays($date) > ConsultationAvailability::MAX_ADVANCE_DAYS) {
            throw ValidationException::withMessages([
                'consultation_date' => ConsultationRules::message('date_far', $locale, ['max' => (string) ConsultationAvailability::MAX_ADVANCE_DAYS]),
            ]);
        }

        if (! in_array($data['time_slot'], $this->availability->slotsFor($company), true)) {
            throw ValidationException::withMessages(['time_slot' => ConsultationRules::message('slot_unknown', $locale)]);
        }

        if ($this->availability->isSlotInPast($company, $date, $data['time_slot'])) {
            throw ValidationException::withMessages(['time_slot' => ConsultationRules::message('slot_past', $locale)]);
        }

        if (! $this->availability->fitsParty($area, (int) $data['participants'])) {
            throw ValidationException::withMessages(['participants' => ConsultationRules::message('capacity', $locale)]);
        }

        $service = filled($data['product_slug'] ?? null)
            ? $company->products()->where('is_active', true)->where('is_unavailable', false)->where('slug', $data['product_slug'])->first()
            : null;

        if (filled($data['product_slug'] ?? null) && ! $service) {
            throw ValidationException::withMessages(['product_slug' => ConsultationRules::message('service_unknown', $locale)]);
        }

        if (filled($data['plan_key'] ?? null) && (! $service || ! collect($service->subscription_plans)->contains('key', $data['plan_key']))) {
            throw ValidationException::withMessages(['plan_key' => ConsultationRules::message('service_unknown', $locale)]);
        }

        return [$area, $date, $service];
    }
}
