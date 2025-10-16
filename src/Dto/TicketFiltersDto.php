<?php

namespace App\Dto;

use App\Enum\Priority;
use App\Enum\Status;
use App\Validator\AllowedValues;

class TicketFiltersDto
{
    public function __construct(
        #[AllowedValues(choices: Status::ALL)]
        public readonly string|array $status = "",
        
        #[AllowedValues(choices: Priority::ALL)]
        public readonly string|array $priority = "",
    ) {}

}
