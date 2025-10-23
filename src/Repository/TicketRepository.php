<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Ticket;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

        if ($ticketFiltersDto->startDate && $ticketFiltersDto->endDate) {
            $startDate = new \DateTime($ticketFiltersDto->startDate);
            $endDate = new \DateTime($ticketFiltersDto->endDate);

            if ($ticketFiltersDto->startTime) {
                [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->startTime);

                $startDate->setTime($hour, $minutes, $second);
            }

            $qb
                ->andWhere('t.createdAt >= :startDate')
                ->setParameter('startDate', $startDate);

            if ($ticketFiltersDto->endTime) {
                [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->endTime);

                $endDate->setTime($hour, $minutes, $second);
            }

            $qb
                ->andWhere('t.createdAt <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($ticketFiltersDto->startDate && !$ticketFiltersDto->endDate) {

            $date = new \DateTimeImmutable($ticketFiltersDto->startDate);

            if ($ticketFiltersDto->startTime && $ticketFiltersDto->endTime) {
                [$hourStartTime, $minutesStartTime, $secondStartTime] = explode(':', $ticketFiltersDto->startTime);
                [$hourEndTime, $minutesEndTime, $secondEndTime] = explode(':', $ticketFiltersDto->endTime);

                $startAt = $date->setTime($hourStartTime, $minutesStartTime, $secondStartTime);
                $endAt = $date->setTime($hourEndTime, $minutesEndTime, $secondEndTime);

                $qb
                    ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', $startAt)
                    ->setParameter('endDate', $endAt);
            } else if ($ticketFiltersDto->startTime && !$ticketFiltersDto->endTime) {
                $qb
                    ->andWhere('t.createdAt >= :startDate')
                    ->setParameter('startDate', $date);
            } else if (!$ticketFiltersDto->startTime && $ticketFiltersDto->endTime) {
                [$hourEndTime, $minutesEndTime, $secondEndTime] = explode(':', $ticketFiltersDto->endTime);

                $endDate = $date->setTime($hourEndTime, $minutesEndTime, $secondEndTime);

                $qb
                    ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', $date)
                    ->setParameter('endDate', $endDate);
            } else {
                $qb
                    ->andWhere('t.createdAt >= :startDate')
                    ->setParameter('startDate', $date);
            }
        }

        $countQb = clone $qb;
        $count = (int) $countQb->select('count(t.id)')->getQuery()->getSingleScalarResult();

        $this->paginate($qb, $paginationDto);

        $tickets = $qb->getQuery()->getResult();

        return [$tickets, $count];
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

    //    /**
    //     * @return Ticket[] Returns an array of Ticket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ticket
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
