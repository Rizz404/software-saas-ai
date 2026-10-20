<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * The demo company owner's email, used by the other seeders to look them up.
     */
    public const OWNER_EMAIL = 'owner@softwaresaasai.test';

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo users may only be seeded in local/testing. Use saas:create-admin in production.');
        }

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => self::OWNER_EMAIL],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info('Demo login: '.self::OWNER_EMAIL.' — password: password');
    }
}
