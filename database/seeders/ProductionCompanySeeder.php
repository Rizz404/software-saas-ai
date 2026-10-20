<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class ProductionCompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(['slug' => 'software-saas-ai'], [
            'name' => 'Software SaaS AI',
            'slug' => 'software-saas-ai',
            'description' => 'Profil studio SaaS. Lengkapi produk, konsultan, kontak, dan kebijakan sebelum dipublikasikan.',
            'translations' => null,
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'address' => null,
            'phone' => null,
            'whatsapp' => null,
            'email' => null,
            'instagram' => null,
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'default_locale' => 'id',
            'opening_time' => '09:00',
            'closing_time' => '17:00',
            'public_status' => 'draft',
        ]);
    }
}
