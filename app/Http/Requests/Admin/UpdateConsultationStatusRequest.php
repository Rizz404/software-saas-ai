<?php

namespace App\Http\Requests\Admin;

use App\Modules\Consultation\Enums\ConsultationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * The company team confirms or cancels a pending consultation; it never goes back
 * to pending.
 */
class UpdateConsultationStatusRequest extends FormRequest
{
    /**
     * A record of another company is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('consultation');

        if ($record) {
            Gate::authorize('update', $record);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([ConsultationStatus::Confirmed->value, ConsultationStatus::Cancelled->value])],
        ];
    }
}
