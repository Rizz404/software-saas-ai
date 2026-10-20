<?php

namespace App\Policies;

use App\Models\Consultant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultantPolicy extends CompanyScopedPolicy
{
    public function update(User $user, Consultant $consultant): Response
    {
        return $this->ownsCompany($user, $consultant->company_id);
    }

    public function delete(User $user, Consultant $consultant): Response
    {
        return $this->ownsCompany($user, $consultant->company_id);
    }
}
