<?php

namespace App\Dto;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Ticket::class)]
class TicketInputPostDto
{
    public function __construct(
        #[Assert\NotBlank()]
        public ?string $title,
        #[Assert\NotBlank()]
        public ?string $description,
        public Status $status = Status::Open,
        public Priority $priority = Priority::Low,
    ) {
    }
}
