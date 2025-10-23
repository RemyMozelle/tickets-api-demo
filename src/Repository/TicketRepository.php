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

    public function getTickets(PaginationDto $paginationDto, array $filters = [], TicketFiltersDto $ticketFiltersDto): mixed
    {
        $qb = $this->createQueryBuilder('t');

        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $qb
                    ->andWhere('t.status in (:status)')
                    ->setParameter('status', $filters['status']);
            } else {
                $qb
                    ->andWhere('t.status = :status')
                    ->setParameter('status', $filters['status']);
            }
        }

        if (isset($filters['priority'])) {
            if (is_array($filters['priority'])) {
                $qb
                    ->andWhere('t.priority in (:priority)')
                    ->setParameter('priority', $filters['priority']);
            } else {
                $qb
                    ->andWhere('t.priority = :priority')
                    ->setParameter('priority', $filters['priority']);
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
            $startDate = new \DateTime($ticketFiltersDto->startDate);

            if (!$ticketFiltersDto->startTime && !$ticketFiltersDto->endTime) {
                $qb
                    ->andWhere('t.createdAt >= :startDate')
                    ->setParameter('startDate', $startDate);
            } else if ($ticketFiltersDto->startTime) {
                [$hour, $minutes, $second] = explode(':', $ticketFiltersDto->startTime);
                $startDate->setTime($hour, $minutes, $second);

                if ($ticketFiltersDto->startTime) {
                    if ($ticketFiltersDto->endTime) {
                        $dateEnd = new \DateTimeImmutable($ticketFiltersDto->startDate . ' ' . $ticketFiltersDto->endTime);

                        $qb
                            ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                            ->setParameter('startDate', $startDate)
                            ->setParameter('endDate', $dateEnd);
                    } else {

                        $qb
                            ->andWhere('t.createdAt >= :startDate')
                            ->setParameter('startDate', $startDate);
                    }
                } else {

                    $qb
                        ->andWhere('t.createdAt >= :startDate')
                        ->setParameter('startDate', $startDate);
                }
            } else if ($ticketFiltersDto->endTime) {
                $date = new \DateTimeImmutable($ticketFiltersDto->startDate . ' ' . $ticketFiltersDto->endTime);

                $qb
                    ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                    ->setParameter('endDate', $date)
                    ->setParameter('startDate', $ticketFiltersDto->startDate);
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
