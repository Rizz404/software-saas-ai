<?php

namespace App\Modules\Consultants\Enums;

enum ConsultantSpecialty: string
{
    case Solution = 'solution';
    case Cloud = 'cloud';
    case Ai = 'ai';
    case Security = 'security';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
