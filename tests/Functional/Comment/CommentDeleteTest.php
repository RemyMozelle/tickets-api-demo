<?php

namespace App\Tests\Functional\Comment;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\SecurityBundle\Security;

class CommentDeleteTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessDeleteTicketCommentData')]
    public function testShouldDeleteCommentForATicket(int $commentId, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $commentRepository = $this->getService(CommentRepository::class);

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentId);

        $client->jsonRequest(method: 'DELETE', uri: $uri);
        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $contentFromResponse = ApiHelper::getResponseDecoded($client);

        // check Json
        $this->assertSame(null, $contentFromResponse);

        // check BDD
        $this->assertNull($commentRepository->find($commentId));
    }

    public static function provideSuccessDeleteTicketCommentData(): \Generator
    {
        yield 'Should delete a comment' => [
            3,
            204,
        ];
    }

    public function testShouldReturnNotFoundWhenDeletingUnknownComment(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, 10000);

        $client->jsonRequest(method: 'DELETE', uri: $uri);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowUserToDeleteCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $security = $this->getService(Security::class);

        $authenticatedUser = $security->getUser();

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
        ]);

        $this->assertNotNull($commentToDelete);

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToDelete->getId());

        $client->jsonRequest(method: 'DELETE', uri: $uri);

        $this->assertResponseStatusCodeSame(204);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNull($commentAfterRequest);
    }

    public function testShouldDenyUserToDeleteCommentWhenNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToDelete);

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToDelete->getId());

        $client->jsonRequest(method: 'DELETE', uri: $uri);

        $this->assertResponseStatusCodeSame(403);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNotNull($commentAfterRequest);
    }

    public function testShouldDenyUserToDeleteCommentWhenIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $commentRepository = $this->getService(CommentRepository::class);
        $userRepository = $this->getService(UserRepository::class);
        $security = $this->getService(Security::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToDelete);

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToDelete->getId());

        $client->jsonRequest(method: 'DELETE', uri: $uri);

        $this->assertResponseStatusCodeSame(401);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNotNull($commentAfterRequest);
    }
}
