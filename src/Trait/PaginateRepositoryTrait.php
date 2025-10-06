<?php

namespace App\Trait;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

trait PaginateRepositoryTrait
{
    protected function paginate(QueryBuilder &$qb, int $page = 1, int $limit): void
    {
        $offset = ($page <= 1 ? $page = 0 : $page - 1) * $limit;

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }
}
