<?php

namespace App\Repository;

use App\Dto\PaginationDto;
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

    public function getTickets(PaginationDto $paginationDto, array $filters = []): mixed
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

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            if (isset($filters['start_time'])) {
                $date = new \DateTimeImmutable($filters['start_date'] . ' ' . $filters['start_time']);
            } else {
                $date = new \DateTimeImmutable($filters['start_date']);
            }

            $qb
                ->andWhere('t.createdAt >= :startDate')
                ->setParameter('startDate', $date);

            if (isset($filters['end_time'])) {
                $date = new \DateTimeImmutable($filters['end_date'] . ' ' . $filters['end_time']);
            } else {
                $date = new \DateTimeImmutable($filters['end_date']);
            }

            $qb
                ->andWhere('t.createdAt <= :endDate')
                ->setParameter('endDate', $date);
        }

        if (isset($filters['start_date']) && !isset($filters['end_date'])) {

            if (!isset($filters['start_time']) && !isset($filters['end_time'])) {
                $date = new \DateTimeImmutable($filters['start_date']);

                $qb
                    ->andWhere('t.createdAt >= :startDate')
                    ->setParameter('startDate', $date);
            } else if (isset($filters['start_time'])) {
                if (isset($filters['start_time'])) {
                    if ($filters['end_time']) {
                        $dateStart = new \DateTimeImmutable($filters['start_date'] . ' ' . $filters['start_time']);
                        $dateEnd = new \DateTimeImmutable($filters['start_date'] . ' ' . $filters['end_time']);


                        $qb
                            ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                            ->setParameter('startDate', $dateStart)
                            ->setParameter('endDate', $dateEnd);
                    } else {
                        $date = new \DateTimeImmutable($filters['start_date'] . ' ' . $filters['start_time']);

                        $qb
                            ->andWhere('t.createdAt >= :startDate')
                            ->setParameter('startDate', $date);
                    }
                } else {
                    $date = new \DateTimeImmutable($filters['start_date']);

                    $qb
                        ->andWhere('t.createdAt >= :startDate')
                        ->setParameter('startDate', $date);
                }
            } else if (isset($filters['end_time'])) {
                $date = new \DateTimeImmutable($filters['start_date'] . ' ' . $filters['end_time']);

                $qb
                    ->andWhere('t.createdAt BETWEEN :startDate AND :endDate')
                    ->setParameter('endDate', $date)
                    ->setParameter('startDate', $filters['start_date']);
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
