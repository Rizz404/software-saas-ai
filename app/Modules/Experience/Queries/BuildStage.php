<?php

namespace App\Modules\Experience\Queries;

use App\Models\Company;
use App\Models\CompanyKnowledgeItem;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use App\Modules\Experience\Presenters\SceneNarrations;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Presenters\StageNavigation;

/**
 * Everything the shared stage shell needs, for whichever scene the guest has
 * walked into.
 */
class BuildStage
{
    public function __construct(
        private readonly ConsultationHandover $handover,
        private readonly ConsultationAvailability $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Company $company, string $locale, string $scene): array
    {
        $products = $company->products()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $consultants = $company->consultants()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $company->knowledgeItems()->where('is_active', true)
            ->whereIn('category', [CompanyKnowledgeItem::CATEGORY_FACILITIES, CompanyKnowledgeItem::CATEGORY_EVENTS, CompanyKnowledgeItem::CATEGORY_LOCATION])
            ->orderBy('sort_order')->get()->values();

        $labels = StageCopy::labels($locale);

        return [
            'company' => $company,
            'products' => $products,
            'consultants' => $consultants,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => StageBackdrop::for($scene),
            'navItems' => StageNavigation::for($company, $locale, $labels),
            'supportedLocales' => config('company.locales'),
            'labels' => $labels,
            'stageCopy' => StageCopy::copy($locale),
            'terms' => StageCopy::terms($locale),
            'wizard' => StageCopy::wizard($locale),
            'narration' => StageCopy::narration($locale),
            'productsNarrations' => SceneNarrations::products($company, $products, $locale),
            'consultantsNarrations' => SceneNarrations::consultants($company, $consultants, $locale),
            'staffLinks' => $this->handover->forStaff($company, $locale),
            'today' => now($company->timezone)->toDateString(),
            'maxAdvanceDays' => ConsultationAvailability::MAX_ADVANCE_DAYS,
            'slots' => collect($this->availability->slotsFor($company))
                ->map(fn (string $slot) => ['value' => $slot, 'label' => ConsultationAvailability::slotRange($slot)])
                ->all(),
            'infoItems' => $company->knowledgeItems()->where('is_active', true)
                ->whereIn('category', [CompanyKnowledgeItem::CATEGORY_GENERAL, CompanyKnowledgeItem::CATEGORY_POLICIES, CompanyKnowledgeItem::CATEGORY_FAQ])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => SceneNarrations::scenes($company, $facilities, $locale),
        ];
    }
}
