<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Consultation;
use App\Modules\Assistant\Tools\Support\CatalogSummaries;
use App\Modules\Assistant\Tools\Support\ConsultationInput;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;
use App\Modules\Consultation\Actions\CreateConsultationRequest;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use Carbon\CarbonImmutable;

/**
 * Only ever creates a pending request for the company to confirm; it never
 * confirms a consultation and takes no payment.
 */
final class CreateConsultationRequestTool implements Tool
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ConsultationInput $input,
        private readonly CreateConsultationRequest $create,
        private readonly ConsultationHandover $handover,
    ) {}

    public function name(): string
    {
        return 'create_consultation_request';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Create a pending 60-minute product demo for 1–6 participants only after explicit agreement to the full summary. Include an optional product slug of interest. Re-check capacity, save monthly plans as a snapshot, and return a reference. Only an administrator can confirm; no payment is taken.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_slug' => ['type' => 'string', 'description' => 'Optional product slug from the current catalog'],
                        'plan_key' => ['type' => 'string', 'enum' => ['starter', 'growth', 'scale'], 'description' => 'Optional plan returned by get_product for the chosen product; never activates a subscription'],
                        'consultant_slug' => ['type' => 'string'],
                        'date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                        'time_slot' => ['type' => 'string', 'description' => 'Slot start time, HH:MM'],
                        'participants' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 6],
                        'goal' => ['type' => 'string', 'enum' => Consultation::GOALS],
                        'client_name' => ['type' => 'string'],
                        'client_phone' => ['type' => 'string', 'description' => 'Phone or WhatsApp number. Required unless client_email is given.'],
                        'client_email' => ['type' => 'string', 'description' => 'Required unless client_phone is given.'],
                        'notes' => ['type' => 'string', 'description' => 'Special request, at most 500 characters'],
                    ],
                    'required' => ['consultant_slug', 'date', 'time_slot', 'participants', 'client_name'],
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

        if ($problem = $this->input->guestProblem($arguments)) {
            return new ToolResult($problem);
        }

        $participants = (int) ($arguments['participants'] ?? 0);

        if (! $this->availability->fitsParty($area, $participants)) {
            return new ToolResult("This participant count does not suit that consultant (it supports {$area->min_participants}-{$area->max_participants} participants). Suggest another consultant, or call request_human_handover for a large group.");
        }

        if ($this->availability->isSlotInPast($company, $date, $slot)) {
            return new ToolResult('That time slot has already started. Ask the guest for a later slot.');
        }

        $serviceSlug = trim((string) ($arguments['product_slug'] ?? ''));
        $service = $serviceSlug !== '' ? CatalogSummaries::productBySlug($company, $serviceSlug) : null;

        if ($serviceSlug !== '' && (! $service || $service->is_unavailable)) {
            return new ToolResult('That product is unavailable. Search the current catalog before submitting.');
        }

        $guestName = trim((string) $arguments['client_name']);

        if ($existing = $this->alreadyRequested($context, $area->id, $date, $slot, $participants, $guestName, $service?->id)) {
            return $this->confirmation($context, $existing, alreadyRequested: true);
        }

        $goal = in_array($arguments['goal'] ?? null, Consultation::GOALS, true) ? $arguments['goal'] : null;
        $phone = trim((string) ($arguments['client_phone'] ?? '')) ?: null;
        $email = trim((string) ($arguments['client_email'] ?? '')) ?: null;

        $consultation = $this->create->handle($company, $area, [
            'product_slug' => $arguments['product_slug'] ?? null,
            'plan_key' => $arguments['plan_key'] ?? null,
            'consultation_date' => $date,
            'time_slot' => $slot,
            'participants' => $participants,
            'goal' => $goal,
            'client_name' => $guestName,
            'client_email' => $email,
            'client_phone' => $phone,
            'contact_type' => $phone ? 'phone' : 'email',
            'notes' => trim((string) ($arguments['notes'] ?? '')) ?: null,
        ], $context->conversation);

        if (! $consultation) {
            return new ToolResult('No consultation is available in that slot any more — availability may have just changed. Call check_consultation_availability again or offer another slot.');
        }

        return $this->confirmation($context, $consultation);
    }

    /**
     * The model may repeat the call when the guest says yes twice; the same
     * request in the same conversation is answered, not booked again.
     */
    private function alreadyRequested(ToolContext $context, int $areaId, CarbonImmutable $date, string $slot, int $participants, string $guestName, ?int $serviceId): ?Consultation
    {
        return Consultation::query()
            ->where('conversation_id', $context->conversation->id)
            ->where('consultant_id', $areaId)
            ->whereDate('consultation_date', $date->toDateString())
            ->where('time_slot', $slot)
            ->where('participants', $participants)
            ->where('client_name', $guestName)
            ->where('product_id', $serviceId)
            ->whereIn('status', [Consultation::STATUS_PENDING, Consultation::STATUS_CONFIRMED])
            ->first();
    }

    private function confirmation(ToolContext $context, Consultation $consultation, bool $alreadyRequested = false): ToolResult
    {
        $area = $consultation->consultant;

        $payload = [
            'consultation_reference' => $consultation->reference,
            'product_snapshot' => $consultation->product_snapshot,
            'consultant_slug' => $area->slug,
            'name' => $consultation->consultant_name_snapshot,
            'date' => $consultation->consultation_date->toDateString(),
            'time_slot' => $consultation->time_slot,
            'time_range' => ConsultationAvailability::slotRange($consultation->time_slot),
            'participants' => $consultation->participants,
            'consultation_fee_total' => (float) $consultation->consultation_fee_total,
            'currency' => $consultation->currency_snapshot,
            'status' => $consultation->status === Consultation::STATUS_CONFIRMED ? 'confirmed' : 'pending_confirmation',
        ];

        return ToolResult::json(
            $alreadyRequested ? [...$payload, 'note' => 'This request was already created earlier in this conversation; do not book again.'] : $payload,
            [
                'type' => 'consultation_confirmation',
                'consultation' => $payload,
                'handover' => $this->handover->forConsultation($context->company, $consultation, $context->locale),
            ],
        );
    }
}
