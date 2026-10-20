<?php

namespace App\Modules\Consultation\Support;

use App\Models\Company;
use App\Models\Consultation;

/**
 * Builds the WhatsApp / phone / email hand-over links that carry a
 * consultation summary (or a plain "talk to staff" request) to the company team.
 */
class ConsultationHandover
{
    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forConsultation(Company $company, Consultation $consultation, string $locale): array
    {
        $message = $this->consultationMessage($consultation, $locale);

        return $this->links($company, $message, $this->subject('consultation', $locale).' '.$consultation->reference);
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    public function forStaff(Company $company, string $locale): array
    {
        return $this->links($company, $this->subject('staff_message', $locale), $this->subject('staff', $locale));
    }

    /**
     * @return array{whatsapp_url: ?string, phone_url: ?string, email_url: ?string}
     */
    private function links(Company $company, string $message, string $subject): array
    {
        $whatsapp = preg_replace('/\D+/', '', (string) $company->whatsapp);
        $phone = preg_replace('/[^\d+]/', '', (string) $company->phone);

        return [
            'whatsapp_url' => $whatsapp !== '' ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode($message) : null,
            'phone_url' => $phone !== '' ? 'tel:'.$phone : null,
            'email_url' => filled($company->email) ? 'mailto:'.$company->email.'?subject='.rawurlencode($subject).'&body='.rawurlencode($message) : null,
        ];
    }

    private function consultationMessage(Consultation $consultation, string $locale): string
    {
        $consultation->loadMissing('consultant');

        $label = fn (string $key): string => trans('handover.message.'.$key, [], $locale);

        return implode("\n", [
            $label('intro'),
            '',
            $label('name').": {$consultation->client_name}",
            $label('date').': '.$consultation->consultation_date->locale($locale)->translatedFormat('l, j F Y'),
            $label('time').': '.ConsultationAvailability::slotRange($consultation->time_slot),
            $label('participants').": {$consultation->participants}",
            $label('consultants').': '.$consultation->consultant_name_snapshot,
            $label('goal').': '.($consultation->goal ?: '-'),
            $label('special').': '.($consultation->notes ?: '-'),
            '',
            $label('reference').": {$consultation->reference}",
        ]);
    }

    private function subject(string $key, string $locale): string
    {
        return trans('handover.subject.'.$key, [], $locale);
    }
}
