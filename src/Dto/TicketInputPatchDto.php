<?php

namespace App\Dto;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;
use App\Validator\AllowedValues;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Ticket::class)]
class TicketInputPatchDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?Status $status = null,
        public ?Priority $priority = null,
    ) {}

    public function mergeInto(Ticket $ticket): Ticket
    {
        if ($this->title !== null) {
            $ticket->setTitle($this->title);
        }

        if ($this->description !== null) {
            $ticket->setDescription($this->description);
        }

        if ($this->status !== null) {
            $ticket->setStatus($this->status);
        }

        if ($this->priority !== null) {
            $ticket->setPriority($this->priority);
        }

        return $ticket;
    }
}

