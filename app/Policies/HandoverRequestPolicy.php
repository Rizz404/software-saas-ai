<?php

namespace App\Policies;

use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HandoverRequestPolicy extends CompanyScopedPolicy
{
    public function view(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsCompany($user, $handover->conversation->company_id);
    }

    public function update(User $user, HandoverRequest $handover): Response
    {
        return $this->ownsCompany($user, $handover->conversation->company_id);
    }
}
