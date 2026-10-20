<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResolvesCurrentCompany
{
    protected function currentCompany(Request $request): Company
    {
        $company = $request->user()->currentCompany();

        if (! $company) {
            throw new HttpException(403, 'No company is linked to this account yet.');
        }

        return $company;
    }
}
