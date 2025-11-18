<?php

namespace App\Response;

use App\Interface\PaginationInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginateCollection
{
   public function __construct(
        private Paginator $paginator,
        private PaginationInterface $paginatorInterface,
        public int $total,
    ) {}

    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    public function getResults(): array
    {
        return iterator_to_array($this->paginator->getIterator());
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->paginatorInterface->limit);
    }

    public function getPage(): int
    {
        return $this->paginatorInterface->page;
    }

    public function getLimit(): int
    {
        return $this->paginatorInterface->limit;
    }

    public function getMeta(): array
    {
        return [
            'total' => $this->total,
            'per_page' => $this->paginatorInterface->limit,
            'current_page' => $this->paginatorInterface->page,
            'total_pages' => $this->getTotalPages(),
        ];
    }

}
