<?php

namespace App\Enum;

enum Status : string 
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';
}
