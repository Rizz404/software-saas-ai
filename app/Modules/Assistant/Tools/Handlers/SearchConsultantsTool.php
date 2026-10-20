<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Consultant;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Support\ConsultationAvailability;

final class SearchConsultantsTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
    ) {}

    public function name(): string
    {
        return 'search_consultants';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Find consultants and live 60-minute consultation slots for a date and 1–6 participants. Optionally filter by specialty or start time. One consultant handles one consultation per slot.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'date' => ['type' => 'string', 'description' => 'Consultation date, YYYY-MM-DD'],
                        'participants' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 6],
                        'time_slot' => ['type' => 'string', 'description' => 'Optional slot start time, HH:MM, one of the consultation slots'],
                        'specialty' => ['type' => 'string', 'enum' => Consultant::specialties(), 'description' => 'Optional preference'],
                    ],
                    'required' => ['date', 'participants'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $company = $context->company;
        $date = $this->input->parseDate($company, (string) ($arguments['date'] ?? ''));

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($company, $date)) {
            return new ToolResult($error);
        }

        $participants = (int) ($arguments['participants'] ?? 0);
        $slot = $arguments['time_slot'] ?? null;
        $areaType = $arguments['specialty'] ?? null;

        if ($slot && ! $this->input->isKnownSlot($company, (string) $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($company));
        }

        $matches = [];

        foreach ($company->consultants()->where('is_active', true)->with('images')->orderBy('sort_order')->get() as $area) {
            if (! $this->availability->fitsParty($area, $participants) || ($areaType && $area->specialty !== $areaType)) {
                continue;
            }

            $slots = $this->availability->availableSlots($area, $date);

            if ($slot) {
                $slots = array_values(array_intersect($slots, [$slot]));
            }

            if ($slots === []) {
                continue;
            }

            $matches[] = [
                'consultant_slug' => $area->slug,
                ...CatalogSummaries::consultant($area, $company, $context->locale),
                'date' => $date->toDateString(),
                'available_slots' => array_map(fn (string $s) => ['time_slot' => $s, 'time_range' => ConsultationAvailability::slotRange($s)], $slots),
            ];
        }

        if ($matches === []) {
            return new ToolResult('No consultant has a available consultation for that date, participant count and time. Tell the guest honestly and offer a different time or date, or call request_human_handover for a large group.');
        }

        return ToolResult::json($matches, [
            'type' => 'consultants_results',
            'date' => $date->toDateString(),
            'participants' => $participants,
            'areas' => $matches,
        ]);
    }
}
