<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Support\ConsultationAvailability;

final class CheckConsultationAvailabilityTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
    ) {}

    public function name(): string
    {
        return 'check_consultation_availability';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Read live availability and consultation fee for a consultant on a date and time. Always use before stating a slot or fee is available. Slots are 60 minutes.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'consultant_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'participants' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 6],
                    ],
                    'required' => ['consultant_slug', 'date', 'time_slot'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $company = $context->company;
        $area = CatalogSummaries::consultantBySlug($company, (string) ($arguments['consultant_slug'] ?? ''));

        if (! $area) {
            return new ToolResult('Consultant not found.');
        }

        $date = $this->input->parseDate($company, (string) ($arguments['date'] ?? ''));
        $slot = (string) ($arguments['time_slot'] ?? '');

        if (! $date) {
            return new ToolResult('date must be a valid YYYY-MM-DD date.');
        }

        if ($error = $this->input->dateProblem($company, $date)) {
            return new ToolResult($error);
        }

        if (! $this->input->isKnownSlot($company, $slot)) {
            return new ToolResult($this->input->unknownSlotMessage($company));
        }

        $participants = isset($arguments['participants']) ? (int) $arguments['participants'] : null;

        if ($participants !== null && ! $this->availability->fitsParty($area, $participants)) {
            return new ToolResult("This participant count does not suit that consultant (it supports {$area->min_participants}-{$area->max_participants} participants). Suggest another consultant, or call request_human_handover for a large group.");
        }

        $quote = $this->availability->isSlotInPast($company, $date, $slot)
            ? null
            : $this->availability->quote($area, $date, $slot);

        if (! $quote) {
            $alternatives = array_map(
                fn (string $s) => ['time_slot' => $s, 'time_range' => ConsultationAvailability::slotRange($s)],
                $this->availability->availableSlots($area, $date),
            );

            return ToolResult::json([
                'available' => false,
                'consultant_slug' => $area->slug,
                'message' => 'No available consultation in this consultant for that slot.',
                'other_available_slots_that_day' => $alternatives,
            ], ['type' => 'availability', 'available' => false, 'consultant_slug' => $area->slug]);
        }

        $result = [
            'available' => true,
            'consultant_slug' => $area->slug,
            'name' => $area->translatedName($context->locale),
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_sessions' => $quote['available_sessions'],
            'consultation_fee' => $quote['fee'],
            'project_budget_from' => $quote['project_budget_from'],
            'currency' => $company->currency,
        ];

        return ToolResult::json($result, ['type' => 'availability', 'available' => true, 'quote' => $result]);
    }
}
