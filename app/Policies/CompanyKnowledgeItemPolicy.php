<?php

namespace App\Policies;

use App\Models\CompanyKnowledgeItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyKnowledgeItemPolicy extends CompanyScopedPolicy
{
    public function update(User $user, CompanyKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsCompany($user, $knowledgeItem->company_id);
    }

    public function delete(User $user, CompanyKnowledgeItem $knowledgeItem): Response
    {
        return $this->ownsCompany($user, $knowledgeItem->company_id);
    }
}
