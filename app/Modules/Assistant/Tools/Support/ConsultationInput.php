<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\Company;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Validation\ConsultationRules;
use Carbon\CarbonImmutable;

/**
 * Checks the date and time slot the model passes to the consultation tools, and
 * words the problem so the model can ask the guest for something that works.
 */
class ConsultationInput
{
    public function __construct(private readonly ConsultationAvailability $availability) {}

    public function parseDate(Company $company, string $value): ?CarbonImmutable
    {
        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value, $company->timezone);

            return $date && $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A reason the date cannot be booked, or null when it is fine.
     */
    public function dateProblem(Company $company, CarbonImmutable $date): ?string
    {
        $today = CarbonImmutable::now($company->timezone)->startOfDay();

        if ($date->lessThan($today)) {
            return 'That date is in the past. Ask the guest for a date from today onwards.';
        }

        if ($today->diffInDays($date) > ConsultationAvailability::MAX_ADVANCE_DAYS) {
            return 'Consultations can only be requested up to '.ConsultationAvailability::MAX_ADVANCE_DAYS.' days ahead. Offer an earlier date, or call request_human_handover for something further out.';
        }

        return null;
    }

    /**
     * A reason the guest's details cannot be used, or null when they are fine.
     * The model decides what to pass, so the same limits as the wizard apply.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function guestProblem(array $arguments): ?string
    {
        if (isset($arguments['participants']) && (! is_int($arguments['participants']) || $arguments['participants'] < 1 || $arguments['participants'] > 6)) {
            return 'participants must be an integer from 1 to 6.';
        }

        $name = trim((string) ($arguments['client_name'] ?? ''));
        $phone = trim((string) ($arguments['client_phone'] ?? ''));
        $email = trim((string) ($arguments['client_email'] ?? ''));

        if ($name === '' || mb_strlen($name) > 100) {
            return 'client_name is missing or too long (100 characters at most). Ask the guest for their name.';
        }

        if ($phone === '' && $email === '') {
            return 'No way to reach the guest. Ask for a phone or WhatsApp number, or an email address, so the company can confirm.';
        }

        if ($phone !== '' && ! preg_match(ConsultationRules::PHONE_PATTERN, $phone)) {
            return 'That phone number does not look valid. Ask the guest to check it.';
        }

        if ($email !== '' && (mb_strlen($email) > 120 || ! filter_var($email, FILTER_VALIDATE_EMAIL))) {
            return 'That email address does not look valid. Ask the guest to check it.';
        }

        if (mb_strlen((string) ($arguments['notes'] ?? '')) > 500) {
            return 'notes is too long (500 characters at most). Shorten it, or call request_human_handover for a detailed request.';
        }

        return null;
    }

    public function isKnownSlot(Company $company, string $slot): bool
    {
        return in_array($slot, $this->availability->slotsFor($company), true);
    }

    public function unknownSlotMessage(Company $company): string
    {
        return 'time_slot must be one of the consultation slots: '.implode(', ', $this->availability->slotsFor($company)).'.';
    }
}
