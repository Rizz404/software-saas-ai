<?php

namespace App\Modules\Knowledge\Queries;

use App\Models\Company;
use App\Models\CompanyKnowledgeItem;
use App\Support\SearchText;
use Illuminate\Support\Collection;

/**
 * The company's approved knowledge base, searched by keyword. The only source the
 * AI SaaS Consultant may state company facts from.
 */
class SearchKnowledge
{
    /**
     * @return Collection<int, CompanyKnowledgeItem>
     */
    public function handle(Company $company, string $query, ?string $category = null, int $limit = 5): Collection
    {
        $query = SearchText::normalize($query);

        return $company->knowledgeItems()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->get()
            ->filter(fn (CompanyKnowledgeItem $item) => SearchText::matches(
                $item->title.' '.$item->body.' '.implode(' ', $item->tags ?? []),
                $query,
            ))
            ->take($limit);
    }
}
