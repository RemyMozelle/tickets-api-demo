<?php

namespace App\Dto;

use App\Entity\Comment;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(target: Comment::class)]
class TicketCommentInputPatchDto
{
    public function __construct(
        public ?string $content,
    ) {}

    public function mergeInto(Comment $content): Comment
    {
        if ($this->content) {
            $content->setContent($this->content);
        }

        return $content;
    }
}
