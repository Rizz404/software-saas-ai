<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy extends CompanyScopedPolicy
{
    public function update(User $user, Product $product): Response
    {
        return $this->ownsCompany($user, $product->company_id);
    }

    public function delete(User $user, Product $product): Response
    {
        return $this->ownsCompany($user, $product->company_id);
    }
}
