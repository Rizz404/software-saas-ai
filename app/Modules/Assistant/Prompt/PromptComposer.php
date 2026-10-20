<?php

namespace App\Modules\Assistant\Prompt;

use App\Models\Company;
use App\Models\Conversation;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Conversation\Support\ContentGuard;

/**
 * Assembles what the model is told on every turn: the system prompt with its
 * hard rules, what the guest is looking at on screen, and the scope reminder
 * that is repeated right after the guest's latest message. The wording itself
 * lives in Templates/*.json (see PromptLibrary); this class only fills in the
 * live values.
 */
class PromptComposer
{
    public function __construct(
        private readonly ContentGuard $guard,
        private readonly ConsultationAvailability $availability,
        private readonly PromptLibrary $library,
    ) {}

    public function systemPrompt(Company $company, Conversation $conversation): string
    {
        $system = $this->library->load('system');
        $variables = [
            'company_name' => $company->name,
            'city' => (string) $company->city,
            'country' => (string) $company->country,
            'locale_name' => $system['languages'][$conversation->locale] ?? $system['fallback_language'],
            'currency' => (string) $company->currency,
            'today' => now($company->timezone)->toDateString(),
            'opening' => substr((string) $company->opening_time, 0, 5).'–'.substr((string) $company->closing_time, 0, 5),
            'slots' => collect($this->availability->slotsFor($company))
                ->map(fn (string $slot) => ConsultationAvailability::slotRange($slot))
                ->implode(', '),
        ];

        $rules = collect($system['rules'])
            ->map(fn (string $rule, int $index) => ($index + 1).'. '.$this->library->render($rule, $variables))
            ->implode("\n");

        return implode("\n\n", [
            $this->library->render($system['intro'], $variables),
            $system['rules_heading']."\n".$rules,
            $this->library->render($system['facts'], $variables),
            $this->uiContext($conversation),
        ]);
    }

    /**
     * Repeats the scope rule right after the guest's latest message, where the
     * model weighs it most. Only the request carries it; it is never stored.
     *
     * @param  list<array{role: string, content: string}>  $history
     * @param  string  $fallbackLocale  the page language, used when the message has no clear one
     * @return list<array{role: string, content: string}>
     */
    public function withScopeReminder(array $history, Company $company, string $fallbackLocale): array
    {
        $last = array_key_last($history);

        if ($last === null || $history[$last]['role'] !== 'user') {
            return $history;
        }

        $reminder = $this->library->load('reminder');
        $language = $reminder['languages'][$this->guard->detectLocale($history[$last]['content'], $fallbackLocale)];

        $history[$last]['content'] .= "\n\n".$this->library->render($reminder['scope'], [
            'language' => $language,
            'company_name' => $company->name,
        ]);

        return $history;
    }

    /**
     * Tells the model what the guest is looking at, per the scene-aware rules
     * of the product spec. Names come from the database, never the guest.
     */
    private function uiContext(Conversation $conversation): string
    {
        $scene = in_array($conversation->current_scene, Conversation::SCENES, true) ? $conversation->current_scene : 'home';
        $product = $conversation->selectedProduct;
        $area = $conversation->selectedConsultant;

        $scenes = $this->library->load('scenes');
        $labels = $scenes['labels'];
        $none = $labels['none'];

        $lines = [
            $labels['title'],
            $this->library->render($labels['scene'], ['scene' => $scene]),
            $this->library->render($labels['product'], ['value' => $product ? "{$product->name} (slug: {$product->slug})" : $none]),
            $this->library->render($labels['consultant'], ['value' => $area ? "{$area->name} (slug: {$area->slug})" : $none]),
            $this->library->render($labels['facility'], ['value' => $conversation->selectedFacility?->title ?? $none]),
        ];

        $draft = $conversation->consultation_state;

        if (is_array($draft) && $draft !== []) {
            $lines[] = $this->library->render($labels['consultation_draft'], [
                'value' => collect($draft)->map(fn ($value, $key) => "{$key}={$value}")->implode(', '),
            ]);
        }

        $lines[] = $this->library->render($labels['guidance'], ['value' => $scenes['guidance'][$scene]]);

        return implode("\n", $lines);
    }
}
