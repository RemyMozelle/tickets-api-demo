<?php

namespace App\Tests\Functional\Comment;

use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CommentDeleteTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessDeleteTicketCommentData')]
    public function testShouldDeleteCommentForATicket(int $commentId, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $client->jsonRequest(method: 'DELETE', uri: sprintf('/comments/%s', $commentId));
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
            204
        ];
    }

    public function testShouldReturnNotFoundWhenDeletingUnknownComment(): void
    {
        $client = AuthHelper::createAuthenticatedClient();


        $client->jsonRequest(method: 'DELETE', uri: sprintf('/comments/%d', 10000));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowUserToDeleteCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $security = static::getContainer()->get(Security::class);

        $authenticatedUser = $security->getUser();

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
        ]);

        $this->assertNotNull($commentToDelete);

        $client->jsonRequest(method: 'DELETE', uri: sprintf('/comments/%d', $commentToDelete->getId()));

        $this->assertResponseStatusCodeSame(204);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNull($commentAfterRequest);
    }

    public function testShouldDenyUserToDeleteCommentWhenNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToDelete);

        $client->jsonRequest(method: 'DELETE', uri: sprintf('/comments/%d', $commentToDelete->getId()));

        $this->assertResponseStatusCodeSame(403);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNotNull($commentAfterRequest);
    }

    public function testShouldDenyUserToDeleteCommentWhenIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToDelete = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToDelete);

        $client->jsonRequest(method: 'DELETE', uri: sprintf('/comments/%d', $commentToDelete->getId()));

        $this->assertResponseStatusCodeSame(401);

        $commentAfterRequest = $commentRepository->find($commentToDelete->getId());

        $this->assertNotNull($commentAfterRequest);
    }
}
