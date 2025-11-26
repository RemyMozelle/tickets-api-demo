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

    public function getCommentsTicketId($ticketId, PaginationDto $paginationDto): mixed
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.ticket = :ticketId')
            ->setParameter('ticketId', $ticketId);

        $countQb = clone $qb;
        $count = (int) $countQb->select('count(c.id)')->getQuery()->getSingleScalarResult();

        $this->paginate($qb, $paginationDto);

        return (new PaginateCollection(new Paginator($qb), $paginationDto, $count));
    }

    public function findByUser(
        int $userId,
        PaginationDto $paginationDto,
    ): array {

        $qb = $this->createQueryBuilder('c');

        $qb
            ->andWhere('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC');

        $this->paginate($qb, $paginationDto);

        return $qb->getQuery()->getResult();
    }
}
