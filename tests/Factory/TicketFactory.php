<?php

namespace App\Tests\Factory;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;

class TicketFactory
{
    /**
     * @param array<mixed> $attributes
     */
    public static function make(array $attributes = []): Ticket
    {
        return (new Ticket())
            ->setTitle($attributes['title'] ?? 'Test ticket')
            ->setDescription($attributes['description'] ?? 'Test description')
            ->setStatus($attributes['status'] ?? Status::Open)
            ->setPriority($attributes['priority'] ?? Priority::High)
            ->setCreatedAt($attributes['created_at'] ?? new \DateTimeImmutable())
            ->setUpdatedAt($attributes['updated_at'] ?? new \DateTimeImmutable());
    }
}
