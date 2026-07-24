<?php

namespace App\Tests\Functional\Comment;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CommentUpdateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessUpdateTicketCommentData')]
    public function testShouldUpdateComment(int $commentId, array $body, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $oldContent = $commentRepository->find($commentId)->getContent();
        $this->assertNotSame($oldContent, $body['content']);

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $body);
        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $comment = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $comment);

        // Check Json
        $this->assertEquals($body['content'], $comment['content']);

        /** @var Comment $commentUpdated */
        $commentUpdated = $commentRepository->find($commentId);
        // Check Bdd
        $this->assertEquals($body['content'], $commentUpdated->getContent());
    }

    public static function provideSuccessUpdateTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            3,
            [
                'content' => 'content updated test 1',
            ],
            200
        ];
    }

    #[DataProvider('provideFailUpdateTicketCommentData')]
    public function testShouldFailToUpdateComment(int $commentId, array $body, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $contentBefore = $commentRepository->find($commentId)->getContent();

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $body);

        $contentAfter = $commentRepository
            ->find($commentId)
            ->getContent();

        $this->assertResponseStatusCodeSame($expectedStatusCode);
        $this->assertSame($contentBefore, $contentAfter);
    }

    public static function provideFailUpdateTicketCommentData(): \Generator
    {
        yield 'Should not update ticket with "content" empty' => [
            3,
            [
                'content' => '',
            ],
            422
        ];

        yield 'Should not update ticket with "content" null' => [
            3,
            [
                'content' => null,
            ],
            422
        ];
    }

    public function testShouldReturnNotFoundWhenUpdatingUnknownComment(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $client->jsonRequest(method: 'PATCH', uri: '/comments/10000', parameters: [
            'content' => '',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowUserToUpdateCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $security = static::getContainer()->get(Security::class);

        $authenticatedUser = $security->getUser();

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
        ]);

        $this->assertNotNull($commentToUpdate);

        $body = [
            'content' => 'currentUser try to comment',
        ];

        $client->jsonRequest(
            method: 'PATCH',
            uri: sprintf('/comments/%d', $commentToUpdate->getId()),
            parameters: $body
        );

        $this->assertResponseStatusCodeSame(200);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            expected: $body['content'],
            actual: $commentAfterRequest->getContent()
        );
    }

    public function testShouldDenyUserToUpdateCommentWhenNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToUpdate);

        $originalContent = $commentToUpdate->getContent();

        $client->jsonRequest(
            method: 'PATCH',
            uri: sprintf('/comments/%d', $commentToUpdate->getId()),
            parameters: [
                'content' => 'currentUser try to comment',
            ]
        );

        $this->assertResponseStatusCodeSame(403);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            expected: $originalContent,
            actual: $commentAfterRequest->getContent()
        );
    }

    public function testShouldDenyUserToUpdateCommentWhenIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToUpdate);

        $originalContent = $commentToUpdate->getContent();

        $client->jsonRequest(
            method: 'PATCH',
            uri: sprintf('/comments/%d', $commentToUpdate->getId()),
            parameters: [
                'content' => 'currentUser try to comment',
            ]
        );

        $this->assertResponseStatusCodeSame(401);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            expected: $originalContent,
            actual: $commentAfterRequest->getContent()
        );
    }
}
