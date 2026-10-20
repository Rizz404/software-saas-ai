<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Company;
use App\Models\Conversation;

/**
 * Remembers where in the UI the guest is, so the solution_consultant can answer for "this
 * service" or the consultation they are filling in. Only values that exist for
 * this company are kept; nothing personal is stored here.
 */
class RememberUiContext
{
    /**
     * @param  array{scene?: ?string, selected_product?: ?string, selected_consultant?: ?string, selected_facility?: ?int, consultation?: ?array<string, mixed>}  $context
     */
    public function handle(Company $company, Conversation $conversation, array $context): void
    {
        $updates = [];

        if (in_array($context['scene'] ?? null, Conversation::SCENES, true)) {
            $updates['current_scene'] = $context['scene'];
        }

        if (filled($context['selected_product'] ?? null)) {
            $product = $company->products()->where('is_active', true)->where('slug', $context['selected_product'])->first();

            if ($product) {
                $updates['selected_product_id'] = $product->id;
            }
        }

        if (filled($context['selected_consultant'] ?? null)) {
            $area = $company->consultants()->where('is_active', true)->where('slug', $context['selected_consultant'])->first();

            if ($area) {
                $updates['selected_consultant_id'] = $area->id;
            }
        }

        if (filled($context['selected_facility'] ?? null)) {
            $facility = $company->knowledgeItems()->where('is_active', true)->whereKey($context['selected_facility'])->first();

            if ($facility) {
                $updates['selected_facility_id'] = $facility->id;
            }
        }

        if (array_key_exists('consultation', $context)) {
            $updates['consultation_state'] = $context['consultation'] ?: null;
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }
}
