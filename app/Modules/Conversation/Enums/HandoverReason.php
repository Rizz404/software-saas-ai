<?php

namespace App\Modules\Conversation\Enums;

/**
 * Why the solution_consultant asked the team to take over.
 */
enum HandoverReason: string
{
    case SpecialRequest = 'special_request';
    case Complaint = 'complaint';
    case GroupConsultation = 'group_consultation';
    case ScopeReview = 'scope_review';
    case CustomProposal = 'custom_proposal';
    case ContractQuestion = 'contract_question';
    case LowConfidence = 'low_confidence';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
