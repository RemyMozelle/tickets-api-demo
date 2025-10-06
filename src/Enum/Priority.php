<?php

namespace App\Enum;

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label()
    {
        return 'priority.' . $this->value;
    }
}
