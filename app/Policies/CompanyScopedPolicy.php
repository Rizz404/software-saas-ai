<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Staff manage the records of their own company and nothing else. A record that
 * belongs to another company is answered with "not found", so its existence is
 * never revealed.
 */
abstract class CompanyScopedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany() !== null;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany() !== null;
    }

    protected function ownsCompany(User $user, ?int $companyId): Response
    {
        $company = $user->currentCompany();

        return $company !== null && $company->id === $companyId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
