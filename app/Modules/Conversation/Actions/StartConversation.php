<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Company;
use App\Models\Conversation;
use Illuminate\Support\Str;

class StartConversation
{
    public function handle(Company $company, string $locale = 'id'): Conversation
    {
        return Conversation::create([
            'company_id' => $company->id,
            'guest_token' => (string) Str::uuid(),
            'locale' => $locale,
            'status' => Conversation::STATUS_ACTIVE,
            'last_message_at' => now(),
        ]);
    }
}
