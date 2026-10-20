<?php

namespace App\Http\Requests\Api;

use App\Http\Middleware\ResolveCompany;
use App\Modules\Consultation\Validation\ConsultationRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Where, when and how many: the first thing the consultation wizard checks.
 */
class QuoteConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ConsultationRules::slot(ResolveCompany::company($this));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ConsultationRules::messages(ResolveCompany::locale($this));
    }
}
