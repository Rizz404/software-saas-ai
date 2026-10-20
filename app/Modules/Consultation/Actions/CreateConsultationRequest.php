<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateConsultationRequest
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    /** @param array<string, mixed> $data */
    public function handle(Company $company, Consultant $area, array $data, ?Conversation $conversation = null): ?Consultation
    {
        if ((int) $area->company_id !== (int) $company->id || ! $area->is_active || ! $this->availability->fitsParty($area, (int) $data['participants'])) {
            return null;
        }

        if ($conversation && (int) $conversation->company_id !== (int) $company->id) {
            return null;
        }

        $date = $data['consultation_date'];
        $today = CarbonImmutable::now($company->timezone)->startOfDay();

        if ($date->lessThan($today) || $today->diffInDays($date) > ConsultationAvailability::MAX_ADVANCE_DAYS || $this->availability->isSlotInPast($company, $date, $data['time_slot'])) {
            return null;
        }

        return DB::transaction(function () use ($company, $area, $data, $conversation, $date): ?Consultation {
            $service = filled($data['product_slug'] ?? null)
                ? $company->products()->where('is_active', true)->where('is_unavailable', false)->where('slug', $data['product_slug'])->lockForUpdate()->first()
                : null;

            if (filled($data['product_slug'] ?? null) && ! $service) {
                return null;
            }

            $plan = $service ? collect($service->subscription_plans)->firstWhere('key', $data['plan_key'] ?? '') : null;

            if (filled($data['plan_key'] ?? null) && ! $plan) {
                return null;
            }

            $slot = ConsultationSlot::query()->where('consultant_id', $area->id)
                ->whereDate('consultation_date', $date->toDateString())->where('time_slot', $data['time_slot'])
                ->lockForUpdate()->first();

            if (! $slot || ! $slot->isAvailable()) {
                return null;
            }

            $held = ConsultationSlot::whereKey($slot->id)->where('reserved_sessions', 0)->update(['reserved_sessions' => 1]);

            if ($held !== 1) {
                return null;
            }

            return Consultation::create([
                'reference' => Consultation::generateReference(),
                'company_id' => $company->id,
                'consultant_id' => $area->id,
                'conversation_id' => $conversation?->id,
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'] ?? null,
                'client_phone' => $data['client_phone'] ?? null,
                'contact_type' => $data['contact_type'] ?? null,
                'consultation_date' => $date->toDateString(),
                'time_slot' => $data['time_slot'],
                'participants' => $data['participants'],
                'goal' => $data['goal'] ?? null,
                'consultation_fee_total' => $area->consultation_fee,
                'consultant_name_snapshot' => $area->name,
                'currency_snapshot' => $company->currency,
                'duration_minutes' => ConsultationAvailability::SLOT_MINUTES,
                'product_id' => $service?->id,
                'product_snapshot' => $service ? [
                    'slug' => $service->slug,
                    'name' => $service->name,
                    'translations' => $service->translations,
                    'estimated_price' => (float) $service->price,
                    'trial_days' => $service->trial_days,
                    'currency' => $company->currency,
                    'price_type' => 'demo_monthly_subscription',
                    'subscription_plans' => $service->subscription_plans,
                    'selected_plan' => $plan,
                ] : null,
                'status' => Consultation::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);
        }, 3);
    }
}
