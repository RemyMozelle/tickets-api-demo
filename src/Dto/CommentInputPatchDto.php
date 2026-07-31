<?php

namespace App\Dto;

use App\Entity\Comment;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Comment::class)]
class CommentInputPatchDto
{
    public function __construct(
        #[Assert\NotBlank()]
        public ?string $content,
    ) {
    }
}
