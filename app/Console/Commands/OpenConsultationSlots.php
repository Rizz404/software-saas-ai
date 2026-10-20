<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\ConsultationSlot;
use App\Modules\Consultation\Support\ConsultationAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class OpenConsultationSlots extends Command
{
    protected $signature = 'saas:open-slots {--company= : Limit to one tenant slug}';

    protected $description = 'Open the next 60 days of consultation slots without changing existing reservations';

    public function handle(): int
    {
        $slug = $this->option('company');

        if ($slug && ! Company::where('slug', $slug)->exists()) {
            $this->error('Company tenant not found.');

            return self::FAILURE;
        }

        Consultant::query()->where('is_active', true)->with('company')
            ->when($slug, fn ($query) => $query->whereHas('company', fn ($company) => $company->where('slug', $slug)))
            ->chunkById(100, function ($consultants): void {
                foreach ($consultants as $consultant) {
                    $today = CarbonImmutable::now($consultant->company->timezone)->startOfDay();
                    $rows = [];

                    for ($day = 0; $day <= ConsultationAvailability::MAX_ADVANCE_DAYS; $day++) {
                        $date = $today->addDays($day);
                        if ($date->isWeekend()) {
                            continue;
                        }

                        foreach (ConsultationAvailability::START_TIMES as $time) {
                            if ($time < substr($consultant->company->opening_time, 0, 5) || $time >= substr($consultant->company->closing_time, 0, 5)) {
                                continue;
                            }
                            $rows[] = [
                                'consultant_id' => $consultant->id,
                                'consultation_date' => $date->toDateString(),
                                'time_slot' => $time,
                                'total_sessions' => 1,
                                'reserved_sessions' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    foreach (array_chunk($rows, 100) as $batch) {
                        ConsultationSlot::upsert($batch, ['consultant_id', 'consultation_date', 'time_slot'], ['total_sessions']);
                    }
                }
            });

        $this->info('Consultation slots opened; existing reservations preserved.');

        return self::SUCCESS;
    }
}
