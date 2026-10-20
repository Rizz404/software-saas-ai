<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy extends CompanyScopedPolicy
{
    public function update(User $user, Consultation $consultation): Response
    {
        return $this->ownsCompany($user, $consultation->company_id);
    }
}
