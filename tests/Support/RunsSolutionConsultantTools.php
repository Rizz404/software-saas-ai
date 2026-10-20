<?php

namespace Tests\Support;

use App\Models\Company;
use App\Models\Conversation;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolExecutor;

/**
 * Runs one AI SaaS Consultant tool the way the model would, against real company data.
 */
trait RunsSolutionConsultantTools
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{text: string, ui: ?array<string, mixed>}
     */
    protected function runTool(Company $company, Conversation $conversation, string $name, array $input): array
    {
        $result = app(ToolExecutor::class)->execute(new ToolContext($company, $conversation, $conversation->locale), $name, $input);

        return ['text' => $result->text, 'ui' => $result->ui];
    }
}
