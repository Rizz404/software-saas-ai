<?php

namespace App\Http\Requests\Admin;

use App\Modules\Consultants\Enums\ConsultantSpecialty;
use App\Support\Text;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Create or update a consultant, with the photos added or removed in the
 * same form.
 */
class ConsultantRequest extends FormRequest
{
    /**
     * A record of another company is "not found" before anything is validated.
     */
    public function authorize(): bool
    {
        $record = $this->route('consultant');

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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.ja.name' => ['nullable', 'string'],
            'translations.ja.description' => ['nullable', 'string'],
            'specialty' => ['required', 'string', Rule::in(ConsultantSpecialty::values())],
            'min_participants' => ['required', 'integer', 'min:1', 'max:6'],
            'max_participants' => ['required', 'integer', 'min:1', 'max:6', 'gte:min_participants'],
            'features' => ['nullable', 'string'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'project_budget_from' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * The columns to write, without the slug (which only a new area gets).
     *
     * @return array<string, mixed>
     */
    public function consultantAttributes(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'translations' => [
                'en' => ['name' => $data['translations']['en']['name'] ?? null, 'description' => $data['translations']['en']['description'] ?? null],
                'ja' => ['name' => $data['translations']['ja']['name'] ?? null, 'description' => $data['translations']['ja']['description'] ?? null],
            ],
            'specialty' => $data['specialty'],
            'min_participants' => $data['min_participants'],
            'max_participants' => $data['max_participants'],
            'features' => Text::slugList($data['features'] ?? null),
            'consultation_fee' => $data['consultation_fee'],
            'project_budget_from' => $data['project_budget_from'] ?? null,
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }

    /**
     * @return list<int>
     */
    public function imageIdsToDelete(): array
    {
        return collect($this->input('delete_images', []))->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return list<array{url: string, alt?: ?string, tags?: ?string}>
     */
    public function newImages(): array
    {
        return collect($this->input('new_images', []))
            ->filter(fn ($row) => filled($row['url'] ?? null))
            ->values()
            ->all();
    }
}
