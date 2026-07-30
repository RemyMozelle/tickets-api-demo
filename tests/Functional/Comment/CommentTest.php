<?php

namespace App\Tests\Functional\Comment;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CommentTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldDenyShowCommentWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/comments/1');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testShouldReturnNotFoundIfNoCommentExist(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/comments/10000');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowShowCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $security = static::getContainer()->get(Security::class);

        /** @var User $authenticatedUser */
        $authenticatedUser = $security->getUser();

        $commentAuthenticatedUser = $commentRepository->findOneBy(['user' => $authenticatedUser]);

        $this->assertNotNull($commentAuthenticatedUser);
        $this->assertSame(
            $authenticatedUser->getId(),
            $commentAuthenticatedUser->getUser()->getId()
        );

        $client->jsonRequest('GET', sprintf('/comments/%s', $commentAuthenticatedUser->getId()));
        $this->assertResponseStatusCodeSame(200);

        $commentFromResponse = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);
    }

    public function testShouldAllowUserToShowCommentWhenCommentIsNotOwnedByUser(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $security = static::getContainer()->get(Security::class);

        /** @var User $authenticatedUser */
        $authenticatedUser = $security->getUser();

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $commentToShow = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentOwner);
        $this->assertNotSame(
            $authenticatedUser->getId(),
            $commentToShow->getUser()->getId()
        );

        $client->jsonRequest('GET', sprintf('/comments/%s', $commentToShow->getId()));
        $this->assertResponseStatusCodeSame(200);

        $commentFromResponse = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);
    }
}
