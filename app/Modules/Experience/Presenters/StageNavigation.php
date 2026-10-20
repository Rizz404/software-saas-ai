<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Company;

/**
 * The main products of the stage. Sections that live on another page are marked
 * so the stage can fade out, as if the solution_consultant were walking the guest over.
 */
final class StageNavigation
{
    /**
     * @param  array<string, string>  $labels
     * @return list<array{key: string, icon: string, label: string, chip: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    public static function for(Company $company, string $locale, array $labels): array
    {
        $tour = StageCopy::narration($locale);
        $url = fn (string $route) => route($route, ['companySlug' => $company->slug, 'lang' => $locale]);

        return [
            ['key' => 'products', 'icon' => 'code', 'label' => $labels['products_heading'], 'chip' => $labels['chip_products'], 'href' => $url('company.products'), 'exit' => true, 'tour' => $tour['tour_products'], 'topic' => $labels['nav_products_q']],
            ['key' => 'consultants', 'icon' => 'team', 'label' => $labels['consultants_heading'], 'chip' => $labels['chip_consultants'], 'href' => $url('company.consultants'), 'exit' => true, 'tour' => $tour['tour_consultants'], 'topic' => $labels['nav_consultants_q']],
            ['key' => 'facilities', 'icon' => 'sparkle', 'label' => $labels['facilities_heading'], 'chip' => $labels['chip_facilities'], 'href' => $url('company.facilities'), 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['nav_facilities_q']],
            ['key' => 'info', 'icon' => 'info', 'label' => $labels['info_heading'], 'chip' => $labels['chip_info'], 'href' => $url('company.info'), 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['nav_info_q']],
            ['key' => 'consultation', 'icon' => 'calendar', 'label' => $labels['consultation_heading'], 'chip' => $labels['chip_consultation'], 'href' => $url('company.consultation'), 'exit' => true, 'tour' => $tour['tour_consultation'], 'topic' => $labels['nav_consultation_q']],
            ['key' => 'staff', 'icon' => 'chat', 'label' => $labels['staff_heading'], 'chip' => $labels['chip_staff'], 'href' => $url('company.staff'), 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['nav_staff_q']],
        ];
    }
}
