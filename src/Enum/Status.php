<?php

namespace App\Enum;

enum Status: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    public const ALL = [
        self::Open->value,
        self::InProgress->value,
        self::Closed->value,
    ];

    public function label()
    {
        return 'status.' . $this->value;
    }

}
