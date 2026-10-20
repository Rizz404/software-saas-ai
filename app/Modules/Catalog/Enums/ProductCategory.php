<?php

namespace App\Modules\Catalog\Enums;

enum ProductCategory: string
{
    case Web = 'web';
    case Mobile = 'mobile';
    case AiAutomation = 'ai_automation';
    case Cloud = 'cloud';
    case Security = 'security';
    case Support = 'support';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
