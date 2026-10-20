<?php

namespace App\Modules\Consultation\Enums;

enum ConsultationGoal: string
{
    case Discovery = 'discovery';
    case Architecture = 'architecture';
    case Automation = 'automation';
    case Modernization = 'modernization';
    case Support = 'support';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
