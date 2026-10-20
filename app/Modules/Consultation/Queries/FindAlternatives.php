<?php

namespace App\Modules\Consultation\Queries;

use App\Models\Company;
use App\Models\Consultant;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * No consultation is available in the requested area and slot: find the other areas that
 * can seat the same party at the same time, and the other slots of the same
 * area that day.
 */
class FindAlternatives
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    /**
     * @return Collection<int, array{type: string, slug?: string, time_slot?: string, name: string, fee?: float}>
     */
    public function handle(Company $company, Consultant $requested, CarbonImmutable $date, string $timeSlot, int $participants, string $locale): Collection
    {
        $otherAreas = $company->consultants()
            ->where('is_active', true)
            ->whereKeyNot($requested->id)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Consultant $area) => $this->availability->fitsParty($area, $participants))
            ->map(function (Consultant $area) use ($date, $timeSlot, $locale) {
                $quote = $this->availability->quote($area, $date, $timeSlot);

                return $quote ? [
                    'type' => 'area',
                    'slug' => $area->slug,
                    'name' => $area->translatedName($locale),
                    'fee' => $quote['fee'],
                ] : null;
            })
            ->filter()
            ->values();

        $otherSlots = collect($this->availability->availableSlots($requested, $date))
            ->reject(fn (string $slot) => $slot === $timeSlot)
            ->map(fn (string $slot) => [
                'type' => 'slot',
                'time_slot' => $slot,
                'name' => ConsultationAvailability::slotRange($slot),
            ])
            ->values();

        return $otherSlots->merge($otherAreas)->values();
    }
}
