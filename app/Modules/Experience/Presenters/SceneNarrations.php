<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Company;
use App\Models\CompanyKnowledgeItem;
use App\Models\Consultant;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What the AI SaaS Consultant says when the guest steps into a scene. Every sentence
 * is built from stored company data, never generated.
 */
final class SceneNarrations
{
    /**
     * @param  Collection<int, Product>  $items
     * @return array{products: ?string, item: array<string, string>}
     */
    public static function products(Company $company, Collection $items, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $terms = StageCopy::terms($locale);
        $money = self::money($company);
        $sentence = self::sentence($locale);
        $separator = $locale === 'ja' ? '、' : ', ';

        if ($items->isEmpty()) {
            return ['products' => null, 'item' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $items->count(), $money($items->min('price'))],
            $templates[$items->count() === 1 ? 'products_one' : 'products']
        );

        $narrations = $items->mapWithKeys(function (Product $item) use ($templates, $terms, $money, $sentence, $separator, $locale) {
            $parts = [$item->translatedName($locale).'.', filled($item->translatedDescription($locale)) ? $sentence(trim($item->translatedDescription($locale))) : null];

            if ($item->delivery_model) {
                $parts[] = str_replace(':delivery_model', $terms['delivery_model'][$item->delivery_model] ?? Str::headline($item->delivery_model), $templates['item_delivery_model']);
            }

            if (! empty($item->prerequisites)) {
                $list = collect($item->prerequisites)->map(fn ($prerequisite) => $terms['prerequisite'][$prerequisite] ?? $prerequisite)->implode($separator);
                $parts[] = str_replace(':list', $list, $templates['item_prerequisites']);
            }

            $parts[] = str_replace(':price', $money($item->price), $templates['item_price']);

            if ($item->is_unavailable) {
                $parts[] = $templates['item_sold_out'];
            }

            return [$item->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['products' => $intro, 'item' => $narrations];
    }

    /**
     * @param  Collection<int, Consultant>  $areas
     * @return array{consultants: ?string, area: array<string, string>}
     */
    public static function consultants(Company $company, Collection $areas, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $money = self::money($company);
        $sentence = self::sentence($locale);

        if ($areas->isEmpty()) {
            return ['consultants' => null, 'area' => []];
        }

        $first = $areas->first()->translatedName($locale);
        $intro = str_replace(
            [':count', ':first'],
            [(string) $areas->count(), $first],
            $templates[$areas->count() === 1 ? 'consultants_one' : 'consultants']
        );

        $narrations = $areas->mapWithKeys(function (Consultant $area) use ($templates, $money, $sentence, $locale) {
            $parts = [$area->translatedName($locale).'.', filled($area->translatedDescription($locale)) ? $sentence(trim($area->translatedDescription($locale))) : null];

            $parts[] = $area->min_participants > 1
                ? str_replace([':min', ':max'], [(string) $area->min_participants, (string) $area->max_participants], $templates['seat_participants'])
                : str_replace(':max', (string) $area->max_participants, $templates['seat_participants_one']);

            $parts[] = (float) $area->consultation_fee > 0
                ? str_replace(':fee', $money($area->consultation_fee), $templates['seat_fee'])
                : $templates['seat_free'];

            if ($area->project_budget_from !== null && (float) $area->project_budget_from > 0) {
                $parts[] = str_replace(':spend', $money($area->project_budget_from), $templates['seat_spend']);
            }

            return [$area->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['consultants' => $intro, 'area' => $narrations];
    }

    /**
     * Intros for the facilities list and the company information scene, built
     * only from stored company data.
     *
     * @param  Collection<int, CompanyKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    public static function scenes(Company $company, Collection $facilities, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$facilities->count() === 1 ? 'facilities_one' : 'facilities']
            );
        }

        $location = collect([$company->city, $company->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':company', $company->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($company->opening_time) && $time($company->closing_time)) {
            $parts[] = str_replace([':open', ':close'], [$time($company->opening_time), $time($company->closing_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @return \Closure(mixed): string
     */
    private static function money(Company $company): \Closure
    {
        return fn ($value) => $company->currency.' '.number_format((float) $value, 0, ',', '.');
    }

    /**
     * @return \Closure(string): string
     */
    private static function sentence(string $locale): \Closure
    {
        return fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');
    }
}
