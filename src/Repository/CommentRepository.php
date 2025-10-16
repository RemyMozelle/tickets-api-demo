<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use App\Entity\Comment;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{

    use PaginateRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getCommentsTicketId($ticketId, PaginationDto $paginationDto): mixed
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $ticketId);

        $this->paginate($qb, $paginationDto->page, $paginationDto->limit);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findByUser(
        int $userId,
        int $page = 1,
        int $limit = 1,
    ): array {

        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC');

        $this->paginate($qb, $page, $limit);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
