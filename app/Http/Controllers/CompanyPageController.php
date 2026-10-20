<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveCompany;
use App\Models\Company;
use App\Models\CompanyKnowledgeItem;
use App\Models\Consultant;
use App\Models\Product;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Queries\BuildStage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The guest-facing stage: one page per scene, all built from the same shell
 * data. The company and the language are resolved by the ResolveCompany middleware.
 */
class CompanyPageController extends Controller
{
    public function __construct(private readonly BuildStage $stage) {}

    public function index(Request $request): View
    {
        $companies = Company::published()->orderBy('name')->get();

        $locale = in_array($request->query('lang'), config('company.locales'), true)
            ? $request->query('lang')
            : ($companies->first()?->default_locale ?? config('company.default_locale'));

        return view('opening', [
            'companies' => $companies,
            'locale' => $locale,
            'supportedLocales' => config('company.locales'),
            'opening' => StageCopy::opening($locale),
            'avatarImage' => StageBackdrop::avatar(),
            'backdrop' => StageBackdrop::for('home'),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company.show', $this->stageData($request, 'home'));
    }

    public function products(Request $request): View
    {
        return view('company.products-index', $this->stageData($request, 'products'));
    }

    public function product(Request $request, string $companySlug, string $itemSlug): View
    {
        $data = $this->stageData($request, 'product');
        $items = $data['products'];
        $index = $items->search(fn (Product $item) => $item->slug === $itemSlug);

        abort_if($index === false, 404);

        return view('company.products-detail', [
            ...$data,
            'product' => $items[$index],
            'itemIndex' => $index,
            ...$this->neighbours($items, $index, 'Item'),
        ]);
    }

    public function consultants(Request $request): View
    {
        return view('company.consultants-index', $this->stageData($request, 'consultants'));
    }

    public function consultant(Request $request, string $companySlug, string $areaSlug): View
    {
        $data = $this->stageData($request, 'consultant');
        $areas = $data['consultants'];
        $index = $areas->search(fn (Consultant $area) => $area->slug === $areaSlug);

        abort_if($index === false, 404);

        return view('company.consultants-detail', [
            ...$data,
            'consultant' => $areas[$index],
            'areaIndex' => $index,
            ...$this->neighbours($areas, $index, 'Area'),
        ]);
    }

    public function facilities(Request $request): View
    {
        return view('company.facilities-index', $this->stageData($request, 'facilities'));
    }

    public function facility(Request $request, string $companySlug, int $facilityId): View
    {
        $data = $this->stageData($request, 'facility');
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (CompanyKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('company.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            ...$this->neighbours($facilities, $index, 'Facility'),
        ]);
    }

    public function info(Request $request): View
    {
        return view('company.info-index', $this->stageData($request, 'info'));
    }

    public function staff(Request $request): View
    {
        return view('company.staff-index', $this->stageData($request, 'staff'));
    }

    public function consultationScene(Request $request): View
    {
        return view('company.consultation-index', [
            ...$this->stageData($request, 'consultation'),
            'preselectedArea' => (string) $request->query('area', ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stageData(Request $request, string $scene): array
    {
        return $this->stage->handle(ResolveCompany::company($request), ResolveCompany::locale($request), $scene);
    }

    /**
     * The previous and next entry around an index, wrapping at both ends.
     *
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function neighbours(Collection $items, int $index, string $suffix): array
    {
        return [
            'previous'.$suffix => $items[($index - 1 + $items->count()) % $items->count()],
            'next'.$suffix => $items[($index + 1) % $items->count()],
        ];
    }
}
