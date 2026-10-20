<?php

namespace App\Modules\Assistant\Tools;

use App\Models\Company;
use App\Models\Conversation;

/**
 * What a tool may act on: one company, one conversation, in the guest's language.
 */
final readonly class ToolContext
{
    public function __construct(
        public Company $company,
        public Conversation $conversation,
        public string $locale,
    ) {}
}
