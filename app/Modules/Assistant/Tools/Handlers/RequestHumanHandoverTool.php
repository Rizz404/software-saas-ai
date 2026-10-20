<?php

namespace App\Modules\Assistant\Tools\Handlers;

use App\Models\Conversation;
use App\Models\HandoverRequest;
use App\Modules\Assistant\Tools\Tool;
use App\Modules\Assistant\Tools\ToolContext;
use App\Modules\Assistant\Tools\ToolResult;

final class RequestHumanHandoverTool implements Tool
{
    public function name(): string
    {
        return 'request_human_handover';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => 'Hand the conversation to the company team for custom proposals, special schedules, more than six participants, contract or authorization questions, complaints, or facts the available data cannot answer. Include an actionable summary.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'reason' => [
                            'type' => 'string',
                            'enum' => ['special_request', 'complaint', 'group_consultation', 'scope_review', 'custom_proposal', 'contract_question', 'low_confidence'],
                        ],
                        'summary' => ['type' => 'string', 'description' => 'What the guest wants and the relevant context gathered so far, written for a team member who has not seen this conversation'],
                    ],
                    'required' => ['reason', 'summary'],
                ],
            ],
        ];
    }

    public function execute(ToolContext $context, array $arguments): ToolResult
    {
        $reason = $arguments['reason'];
        $summary = $arguments['summary'];

        HandoverRequest::create([
            'conversation_id' => $context->conversation->id,
            'reason' => $reason,
            'summary' => $summary,
            'status' => HandoverRequest::STATUS_OPEN,
        ]);

        $context->conversation->update([
            'status' => Conversation::STATUS_HANDED_OVER,
            'handover_summary' => $summary,
        ]);

        return ToolResult::json([
            'ok' => true,
            'message' => 'A team member has been notified and will join this conversation shortly.',
        ], ['type' => 'handover', 'reason' => $reason, 'summary' => $summary]);
    }
}
