<?php

namespace App\Http\Requests\Api;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One guest message plus where in the UI the guest is when they send it.
 */
class SendMessageRequest extends FormRequest
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
        return [
            'guest_token' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:2000'],
            'scene' => ['nullable', Rule::in(Conversation::SCENES)],
            'selected_product' => ['nullable', 'string', 'max:120'],
            'selected_consultant' => ['nullable', 'string', 'max:120'],
            'selected_facility' => ['nullable', 'integer', 'min:1'],
            'consultation' => ['nullable', 'array'],
            'consultation.consultation_date' => ['nullable', 'date_format:Y-m-d'],
            'consultation.time_slot' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'consultation.participants' => ['nullable', 'integer', 'min:1', 'max:6'],
            'consultation.product_slug' => ['nullable', 'string', 'max:120'],
            'consultation.goal' => ['nullable', 'string', 'max:30'],
            'consultation.consultant_slug' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Where the guest is in the UI, as the solution_consultant's conversation remembers it.
     *
     * @return array{scene?: ?string, selected_product?: ?string, selected_consultant?: ?string, selected_facility?: ?int, consultation?: array<string, mixed>}
     */
    public function uiContext(): array
    {
        $data = $this->validated();

        return [
            'scene' => $data['scene'] ?? null,
            'selected_product' => $data['selected_product'] ?? null,
            'selected_facility' => $data['selected_facility'] ?? null,
            'selected_consultant' => $data['selected_consultant'] ?? ($data['consultation']['consultant_slug'] ?? null),
            ...array_key_exists('consultation', $data)
                ? ['consultation' => array_filter($data['consultation'] ?? [], fn ($value) => $value !== null && $value !== '')]
                : [],
        ];
    }
}
