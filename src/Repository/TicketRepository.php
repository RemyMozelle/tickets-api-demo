<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

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

    public function getTickets(int $page = 1, int $limit, array $filters = []): mixed
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

        $this->paginate($qb, $page, $limit);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findByUser(
        int $userId,
        int $page = 1,
        int $limit = 1,
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

        $this->paginate($qb, $page, $limit);

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
