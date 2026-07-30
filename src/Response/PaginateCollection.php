<?php

namespace App\Response;

use App\Interface\PaginationInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template-covariant T of object
 */
class PaginateCollection
{
   /**
    * @param Paginator<T> $paginator
    * @param PaginationInterface $paginatorInterface
    * @param int $total
    */
   public function __construct(
        private Paginator $paginator,
        private PaginationInterface $paginatorInterface,
        public int $total,
    ) {}

    /**
     * @return array<int, T>
     */
    public function getResults():array
    {
        return iterator_to_array($this->paginator->getIterator());
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->getLimit());
    }

    public function getPage(): int
    {
        return $this->paginatorInterface->getPage();
    }

    public function getLimit(): int
    {
        return $this->paginatorInterface->getLimit();
    }

    /**
     * @return array<string, int>
     */
    public function getMeta(): array
    {
        return [
            'total' => $this->total,
            'per_page' => $this->getLimit(),
            'current_page' => $this->getPage(),
            'total_pages' => $this->getTotalPages(),
        ];
    }

}
