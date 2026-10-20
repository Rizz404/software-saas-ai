<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KnowledgeItemRequest;
use App\Models\CompanyKnowledgeItem;
use App\Modules\Knowledge\Enums\KnowledgeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KnowledgeItemController extends Controller
{
    use ResolvesCurrentCompany;

    public function index(Request $request): View
    {
        $company = $this->currentCompany($request);

        $items = $company->knowledgeItems()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.knowledge-items.index', compact('company', 'items'));
    }

    public function create(Request $request): View
    {
        $company = $this->currentCompany($request);

        return view('admin.knowledge-items.form', [
            'company' => $company,
            'item' => new CompanyKnowledgeItem(['category' => CompanyKnowledgeItem::CATEGORY_GENERAL, 'is_active' => true]),
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function store(KnowledgeItemRequest $request): RedirectResponse
    {
        $item = $this->currentCompany($request)->knowledgeItems()->create($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$item->title}\" created.");
    }

    public function edit(Request $request, CompanyKnowledgeItem $knowledgeItem): View
    {
        $company = $this->currentCompany($request);
        Gate::authorize('update', $knowledgeItem);

        return view('admin.knowledge-items.form', [
            'company' => $company,
            'item' => $knowledgeItem,
            'categories' => KnowledgeCategory::values(),
        ]);
    }

    public function update(KnowledgeItemRequest $request, CompanyKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentCompany($request);

        $knowledgeItem->update($request->knowledgeItemAttributes());

        return redirect()->route('admin.knowledge-items.index')->with('status', "Knowledge item \"{$knowledgeItem->title}\" updated.");
    }

    public function destroy(Request $request, CompanyKnowledgeItem $knowledgeItem): RedirectResponse
    {
        $this->currentCompany($request);
        Gate::authorize('delete', $knowledgeItem);

        $knowledgeItem->delete();

        return redirect()->route('admin.knowledge-items.index')->with('status', 'Knowledge item deleted.');
    }
}
