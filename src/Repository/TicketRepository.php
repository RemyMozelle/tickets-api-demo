<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Ticket;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Response\PaginateCollection;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{

    use PaginateRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function getTickets(PaginationDto $paginationDto, TicketFiltersDto $ticketFiltersDto): PaginateCollection
    {
        $qb = $this->createQueryBuilder('t');

        $this->applyTicketFilter($qb, $ticketFiltersDto);

        $this->paginate($qb, $paginationDto);

        $paginator = new Paginator($qb, fetchJoinCollection: false);

        return (new PaginateCollection($paginator, $paginationDto, $paginator->count()));
    }

    public function findByUser(
        int $userId,
        PaginationDto $paginationDto,
        TicketFiltersDto $ticketFiltersDto,
    ): PaginateCollection 
    {
        $qb = $this->createQueryBuilder('t');

        $this->applyTicketFilter($qb, $ticketFiltersDto);

        $qb
            ->andWhere('t.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.createdAt', 'DESC');

        $this->paginate($qb, $paginationDto);

        $paginator = new Paginator($qb, fetchJoinCollection: false);

        return (new PaginateCollection($paginator, $paginationDto, $paginator->count()));
    }

    private function applyTicketFilter(QueryBuilder &$qb, TicketFiltersDto $ticketFiltersDto): void 
    {
        if ($ticketFiltersDto->status) {
            if (is_array($ticketFiltersDto->status)) {
                $qb
                    ->andWhere('t.status in (:status)')
                    ->setParameter('status', $ticketFiltersDto->status);
            } else {
                $qb
                    ->andWhere('t.status = :status')
                    ->setParameter('status', $ticketFiltersDto->status);
            }
        }

        if ($ticketFiltersDto->priority) {
            if (is_array($ticketFiltersDto->priority)) {
                $qb
                    ->andWhere('t.priority in (:priority)')
                    ->setParameter('priority', $ticketFiltersDto->priority);
            } else {
                $qb
                    ->andWhere('t.priority = :priority')
                    ->setParameter('priority', $ticketFiltersDto->priority);
            }
        }

        if ($ticketFiltersDto->startDate) {
            $startDate = new \DateTime($ticketFiltersDto->startDate);
            $startDate->setTime(0, 0, 0);

            if ($ticketFiltersDto->startTime) {
                [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->startTime);
                $startDate->setTime($hour, $minutes, $second);
            }

            $qb
                ->andWhere('t.createdAt >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($ticketFiltersDto->endDate) {
            $endDate = new \DateTime($ticketFiltersDto->endDate);
            $endDate->setTime(23, 59, 59);

            if ($ticketFiltersDto->endTime) {
                [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->endTime);
                $endDate->setTime($hour, $minutes, $second);
            }

            $qb
                ->andWhere('t.createdAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($ticketFiltersDto->endTime && !$ticketFiltersDto->endDate) {
            $endDate = new \DateTime($ticketFiltersDto->startDate);
            [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->endTime);
            $endDate->setTime($hour, $minutes, $second);

            $qb
                ->andWhere('t.createdAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }
    }
}
