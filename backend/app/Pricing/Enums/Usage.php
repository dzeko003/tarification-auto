<?php

declare(strict_types=1);

namespace App\Pricing\Enums;

enum Usage: string
{
    case Personal = 'personnel';
    case Professional = 'professionnel';

    public function label(): string
    {
        return match ($this) {
            self::Personal => 'Usage personnel',
            self::Professional => 'Usage professionnel',
        };
    }
}
