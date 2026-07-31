<?php

namespace App\Repository;

use App\Dto\PaginationDto;
use App\Entity\User;
use App\Response\PaginateCollection;
use App\Trait\PaginateRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use PaginateRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (! $user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()
            ->persist($user);
        $this->getEntityManager()
            ->flush();
    }

    /**
     * @return PaginateCollection<User>
     */
    public function getUsers(PaginationDto $paginationDto): PaginateCollection
    {
        $qb = $this->createQueryBuilder('u');
        $qb->orderBy('u.id', 'asc');

        $countQb = clone $qb;
        $count = (int) $countQb->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->paginate($qb, $paginationDto);

        /** @var Paginator<User> */
        $paginator = new Paginator($qb);

        return new PaginateCollection($paginator, $paginationDto, $count);
    }
}
