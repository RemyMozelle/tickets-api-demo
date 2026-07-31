<?php

namespace App\Dto;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;
use App\ObjectMapper\IsDefinedCondition;
use Symfony\Component\ObjectMapper\Attribute\Map;

// TODO: add updated_at field
#[Map(target: Ticket::class)]
class TicketInputPatchDto
{
    public function __construct(
        #[Map(if: IsDefinedCondition::class)]
        public ?string $title = null,
        #[Map(if: IsDefinedCondition::class)]
        public ?string $description = null,
        #[Map(if: IsDefinedCondition::class)]
        public ?Status $status = null,
        #[Map(if: IsDefinedCondition::class)]
        public ?Priority $priority = null,
    ) {
    }
}
