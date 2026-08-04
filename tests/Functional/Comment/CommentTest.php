<?php

namespace App\Tests\Functional\Comment;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\SecurityBundle\Security;

class CommentTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldDenyShowCommentWhenUserIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, 1);

        $client->jsonRequest('GET', $uri);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testShouldReturnNotFoundIfNoCommentExist(): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, 10000);

        $client->jsonRequest('GET', $uri);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowShowCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $security = $this->getService(Security::class);

        /** @var User $authenticatedUser */
        $authenticatedUser = $security->getUser();

        $commentAuthenticatedUser = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
        ]);

        $this->assertNotNull($commentAuthenticatedUser);
        $this->assertSame(
            $authenticatedUser->getId(),
            $commentAuthenticatedUser->getUser()
                ->getId()
        );

        $uri = sprintf('%s/comments/%s', ApiTestCase::API_PREFIX, $commentAuthenticatedUser->getId());

        $client->jsonRequest('GET', $uri);
        $this->assertResponseStatusCodeSame(200);

        $commentFromResponse = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);
    }

    public function testShouldAllowUserToShowCommentWhenCommentIsNotOwnedByUser(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $userRepository = $this->getService(UserRepository::class);
        $security = $this->getService(Security::class);

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
            $commentToShow->getUser()
                ->getId()
        );

        $uri = sprintf('%s/comments/%s', ApiTestCase::API_PREFIX, $commentToShow->getId());

        $client->jsonRequest('GET', $uri);
        $this->assertResponseStatusCodeSame(200);

        $commentFromResponse = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);
    }
}
