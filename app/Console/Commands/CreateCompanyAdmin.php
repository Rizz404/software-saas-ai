<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateCompanyAdmin extends Command
{
    public const PASSWORD_MIN = 12;

    protected $signature = 'saas:create-admin {--email=} {--name=} {--company= : Tenant slug}';

    protected $description = 'Create an owner account for an existing company without exposing its password';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Use interactive prompts or pipe the password and confirmation through stdin.');

            return self::FAILURE;
        }

        $values = ['email' => $this->option('email'), 'name' => $this->option('name'), 'company' => $this->option('company')];
        $validator = Validator::make($values, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:120'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $company = Company::where('slug', $values['company'])->first();

        if (! $company) {
            $this->error('Company tenant not found.');

            return self::FAILURE;
        }

        $password = $this->secret('Password (minimum '.self::PASSWORD_MIN.' characters)');
        $confirmation = $this->secret('Confirm password');

        if (! is_string($password) || mb_strlen($password) < self::PASSWORD_MIN || $password !== $confirmation) {
            $this->error('Password must meet the minimum length and match its confirmation.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($values, $password, $company): void {
            $user = User::create(['email' => $values['email'], 'name' => $values['name'], 'password' => $password]);
            $company->users()->syncWithoutDetaching([$user->id => ['role' => 'owner', 'status' => 'active']]);
        });

        $this->info('Admin '.$values['email'].' created.');

        return self::SUCCESS;
    }
}
