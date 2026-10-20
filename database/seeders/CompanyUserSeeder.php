<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanyUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', CompanySeeder::NAME)->firstOrFail();
        $owner = User::where('email', UserSeeder::OWNER_EMAIL)->firstOrFail();

        $company->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active'],
        ]);
    }
}
