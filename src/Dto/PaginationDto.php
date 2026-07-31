<?php

namespace App\Dto;

use App\Interface\PaginationInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PaginationDto implements PaginationInterface
{
    public function __construct(
        #[Assert\Positive()]
        public int $page = 1,
        #[Assert\Positive()]
        #[Assert\LessThanOrEqual(12)]
        public int $limit = 12
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
