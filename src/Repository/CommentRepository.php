<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use App\Entity\Comment;
use App\Response\PaginateCollection;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    /**
     * @return PaginateCollection<Comment>
     */
    public function getCommentsTicketId(int $ticketId, PaginationDto $paginationDto): PaginateCollection
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.ticket = :ticketId')
            ->setParameter('ticketId', $ticketId);

        $countQb = clone $qb;
        $count = (int) $countQb->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->paginate($qb, $paginationDto);

        /** @var Paginator<Comment> */
        $paginator = new Paginator($qb);

        return new PaginateCollection($paginator, $paginationDto, $count);
    }

    /**
     * @return PaginateCollection<Comment>
     */
    public function findByUser(
        int $userId,
        PaginationDto $paginationDto,
    ): PaginateCollection {

        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC');

        $this->paginate($qb, $paginationDto);

        /** @var Paginator<Comment> */
        $paginator = new Paginator($qb);

        return new PaginateCollection($paginator, $paginationDto, $paginator->count());
    }
}
