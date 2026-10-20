<?php

namespace App\Modules\Operations\Queries;

use App\Models\Company;
use App\Models\Consultation;
use App\Models\HandoverRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the company team sees first when they sign in: counts of what they manage
 * and the few things waiting for them.
 */
class DashboardMetrics
{
    /**
     * @return array{products: int, consultants: int, knowledge_items: int, pending_consultations: int, open_handovers: int}
     */
    public function counts(Company $company): array
    {
        return [
            'products' => $company->products()->count(),
            'consultants' => $company->consultants()->count(),
            'knowledge_items' => $company->knowledgeItems()->count(),
            'pending_consultations' => $company->consultations()->where('status', Consultation::STATUS_PENDING)->count(),
            'open_handovers' => $this->openHandovers($company)->count(),
        ];
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function recentConsultations(Company $company, int $limit = 5): Collection
    {
        return $company->consultations()->latest()->take($limit)->with('consultant')->get();
    }

    /**
     * @return Collection<int, HandoverRequest>
     */
    public function latestOpenHandovers(Company $company, int $limit = 5): Collection
    {
        return $this->openHandovers($company)->latest()->take($limit)->with('conversation')->get();
    }

    /**
     * @return Builder<HandoverRequest>
     */
    private function openHandovers(Company $company): Builder
    {
        return HandoverRequest::whereHas('conversation', fn ($q) => $q->where('company_id', $company->id))
            ->where('status', HandoverRequest::STATUS_OPEN);
    }
}
