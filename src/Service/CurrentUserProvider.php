<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\LogicException;

class CurrentUserProvider
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function __invoke(): User
    {
        $user = $this->security->getUser();

        if (! $user instanceof User) {
            throw new LogicException('Authenticated user is not a User entity.');
        }

        return $user;
    }
}
