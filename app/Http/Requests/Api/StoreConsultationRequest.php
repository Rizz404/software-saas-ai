<?php

namespace App\Http\Requests\Api;

use App\Http\Middleware\ResolveCompany;
use App\Modules\Consultation\Validation\ConsultationRules;
use Illuminate\Contracts\Validation\Validator;

/**
 * The full consultation request: the slot being asked for plus who is asking.
 */
class StoreConsultationRequest extends QuoteConsultationRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [...parent::rules(), ...ConsultationRules::guest()];
    }

    /**
     * The contact must look like what the guest said it is.
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $locale = ResolveCompany::locale($this);
            $value = $this->input('contact_value');

            if ($this->isEmailContact() && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('contact_value', ConsultationRules::message('contact_email', $locale));
            }

            if (! $this->isEmailContact() && ! preg_match(ConsultationRules::PHONE_PATTERN, $value)) {
                $validator->errors()->add('contact_value', ConsultationRules::message('contact_phone', $locale));
            }
        }];
    }

    public function isEmailContact(): bool
    {
        return $this->input('contact_type') === 'email';
    }
}
