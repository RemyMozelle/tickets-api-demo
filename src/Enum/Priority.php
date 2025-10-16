<?php

namespace App\Enum;

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public const ALL = [
        self::Low->value,
        self::Medium->value,
        self::High->value,
    ];

    public function label()
    {
        return 'priority.' . $this->value;
    }
}
