<?php

namespace App\Dto;

use App\Entity\Comment;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Comment::class)]
class TicketCommentInputPatchDto
{
    public function __construct(
        #[Assert\NotBlank()]
        public ?string $content,
    ) {}
}
