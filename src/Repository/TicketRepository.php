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

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{

    use PaginateRepositoryTrait;

    protected PaginationDto $paginationDto;

    public function __construct(ManagerRegistry $registry, PaginationDto $paginationDto)
    {
        parent::__construct($registry, Ticket::class);
        $this->paginationDto = $paginationDto;
    }

    public function getTickets(PaginationDto $paginationDto, TicketFiltersDto $ticketFiltersDto): mixed
    {
        $qb = $this->createQueryBuilder('t');

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

        $countQb = clone $qb;
        $count = (int) $countQb->select('count(t.id)')->getQuery()->getSingleScalarResult();

        $this->paginate($qb, $paginationDto);

        return (new PaginateCollection(new Paginator($qb), $paginationDto, $count));
    }

    public function findByUser(
        int $userId,
        PaginationDto $paginationDto,
        array $filters = [],
    ): array {

        $qb = $this->createQueryBuilder('t');

        if (isset($filters['status'])) {
            $qb
                ->andWhere('t.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $qb
                ->andWhere('t.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        $qb
            ->andWhere('t.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.createdAt', 'DESC');

        $this->paginate($qb, $paginationDto);

        return $qb
            ->getQuery()
            ->getResult();
    }
}
