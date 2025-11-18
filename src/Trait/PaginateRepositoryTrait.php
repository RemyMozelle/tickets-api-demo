<?php

namespace App\Trait;

use App\Interface\PaginationInterface;
use Doctrine\ORM\QueryBuilder;

trait PaginateRepositoryTrait
{
    protected function paginate(QueryBuilder &$qb, PaginationInterface $pagination): void
    {
        $offset = ($pagination->getPage() - 1) * $pagination->getLimit();

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($pagination->getLimit());
    }
}
