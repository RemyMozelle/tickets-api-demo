<?php

namespace App\Dto;

use Dom\Comment;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Comment::class)]
class TicketCommentInputPostDto
{
    public function __construct(
        #[Assert\NotBlank()]
        public ?string $content,
    ) {}
}
