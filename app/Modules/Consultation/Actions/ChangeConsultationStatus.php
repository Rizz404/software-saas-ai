<?php

namespace App\Modules\Consultation\Actions;

use App\Models\Consultation;
use App\Models\ConsultationSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeConsultationStatus
{
    public function handle(Consultation $consultation, string $status): Consultation
    {
        return DB::transaction(function () use ($consultation, $status): Consultation {
            $locked = Consultation::whereKey($consultation->id)->lockForUpdate()->firstOrFail();

            if (! in_array($status, [Consultation::STATUS_CONFIRMED, Consultation::STATUS_CANCELLED], true) || ($locked->status === Consultation::STATUS_CANCELLED && $status !== Consultation::STATUS_CANCELLED)) {
                throw ValidationException::withMessages(['status' => __('consultation.messages.status_invalid')]);
            }

            if ($status === Consultation::STATUS_CANCELLED && $locked->status !== Consultation::STATUS_CANCELLED) {
                ConsultationSlot::where('consultant_id', $locked->consultant_id)
                    ->whereDate('consultation_date', $locked->consultation_date->toDateString())
                    ->where('time_slot', $locked->time_slot)->where('reserved_sessions', '>', 0)
                    ->decrement('reserved_sessions');
            }

            $locked->update(['status' => $status]);

            return $locked;
        }, 3);
    }
}
