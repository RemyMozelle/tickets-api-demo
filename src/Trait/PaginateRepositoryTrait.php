<?php

namespace App\Trait;

use App\Dto\PaginationDto;
use Doctrine\ORM\QueryBuilder;

trait PaginateRepositoryTrait
{
    protected function paginate(QueryBuilder &$qb, PaginationDto $paginationDto): void
    {
        $page = $paginationDto->page;

        $offset = ($page <= 1 ? $page = 0 : $paginationDto->page - 1) * $paginationDto->limit;

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($paginationDto->limit);
    }
}
