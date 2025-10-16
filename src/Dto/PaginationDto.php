<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDto
{
    public function __construct(
        #[Assert\Positive()]
        public int $page = 1,

        #[Assert\Positive()]
        #[Assert\LessThanOrEqual(12)]
        public int $limit = 12
    ) {
    }
}
