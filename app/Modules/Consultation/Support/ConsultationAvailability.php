<?php

namespace App\Modules\Consultation\Support;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\ConsultationSlot;
use Carbon\CarbonImmutable;

/**
 * The single place where consultation availability and slot quotes are computed.
 * Both the AI SaaS Consultant tools and the guest-facing consultation wizard go
 * through here, so they can never disagree.
 */
class ConsultationAvailability
{
    /**
     * How far ahead a table can be requested.
     */
    public const MAX_ADVANCE_DAYS = 60;

    /**
     * Every slot is a fixed 60-minute sitting.
     */
    public const SLOT_MINUTES = 60;

    public const MAX_PARTICIPANTS = 6;

    public const START_TIMES = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

    /**
     * Whether the participant count suits the consultant.
     */
    public function fitsParty(Consultant $area, int $participants): bool
    {
        return $participants >= max(1, $area->min_participants) && $participants <= min(self::MAX_PARTICIPANTS, $area->max_participants);
    }

    /**
     * The slot start times the company takes consultations for, earliest first.
     *
     * @return list<string>
     */
    public function slotsFor(Company $company): array
    {
        return ConsultationSlot::query()
            ->whereIn('consultant_id', $company->consultants()->where('is_active', true)->select('id'))
            ->distinct()
            ->orderBy('time_slot')
            ->pluck('time_slot')
            ->all();
    }

    /**
     * "11:00–13:00" for a slot that starts at 11:00.
     */
    public static function slotRange(string $slot): string
    {
        $start = CarbonImmutable::createFromFormat('H:i', $slot);

        return $start->format('H:i').'–'.$start->addMinutes(self::SLOT_MINUTES)->format('H:i');
    }

    /**
     * Whether the slot has already begun in the company's own time zone.
     */
    public function isSlotInPast(Company $company, CarbonImmutable $date, string $slot): bool
    {
        $start = CarbonImmutable::parse($date->toDateString().' '.$slot, $company->timezone);

        return $start->lessThanOrEqualTo(CarbonImmutable::now($company->timezone));
    }

    /**
     * Checks one slot. Returns null if there is no available consultation in it, which is
     * the one place availability truth comes from, never the model.
     *
     * @return array{date: string, time_slot: string, time_range: string, available_sessions: int, fee: float, project_budget_from: ?float}|null
     */
    public function quote(Consultant $area, CarbonImmutable $date, string $slot, bool $lockForUpdate = false): ?array
    {
        $query = ConsultationSlot::where('consultant_id', $area->id)
            ->whereDate('consultation_date', $date->toDateString())
            ->where('time_slot', $slot);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $inventory = $query->first();

        if (! $inventory || ! $inventory->isAvailable()) {
            return null;
        }

        return [
            'date' => $date->toDateString(),
            'time_slot' => $slot,
            'time_range' => self::slotRange($slot),
            'available_sessions' => $inventory->availableSessions(),
            'fee' => (float) $area->consultation_fee,
            'project_budget_from' => $area->project_budget_from !== null ? (float) $area->project_budget_from : null,
        ];
    }

    /**
     * Every slot of the day that still has a available consultation and has not started.
     *
     * @return list<string>
     */
    public function availableSlots(Consultant $area, CarbonImmutable $date): array
    {
        $company = $area->company;

        return ConsultationSlot::where('consultant_id', $area->id)
            ->whereDate('consultation_date', $date->toDateString())
            ->orderBy('time_slot')
            ->get()
            ->filter(fn (ConsultationSlot $row) => $row->isAvailable() && ! $this->isSlotInPast($company, $date, $row->time_slot))
            ->pluck('time_slot')
            ->values()
            ->all();
    }
}
