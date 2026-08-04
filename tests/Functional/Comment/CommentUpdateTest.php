<?php

namespace App\Tests\Functional\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\SecurityBundle\Security;

class CommentUpdateTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, mixed> $body
     */
    #[DataProvider('provideSuccessUpdateTicketCommentData')]
    public function testShouldUpdateComment(int $commentId, array $body, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentRepository = $this->getService(CommentRepository::class);
        $oldContent = $commentRepository->find($commentId)
            ->getContent();
        $this->assertNotSame($oldContent, $body['content']);

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentId);

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
            200,
        ];
    }

    /**
     * @param array<string, mixed> $body
     */
    #[DataProvider('provideFailUpdateTicketCommentData')]
    public function testShouldFailToUpdateComment(int $commentId, array $body, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentRepository = $this->getService(CommentRepository::class);
        $contentBefore = $commentRepository->find($commentId)
            ->getContent();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentId);

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
            422,
        ];

        yield 'Should not update ticket with "content" null' => [
            3,
            [
                'content' => null,
            ],
            422,
        ];
    }

    public function testShouldReturnNotFoundWhenUpdatingUnknownComment(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, 10000);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: [
            'content' => '',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldAllowUserToUpdateCommentWhenIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $security = $this->getService(Security::class);

        $authenticatedUser = $security->getUser();

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
        ]);

        $this->assertNotNull($commentToUpdate);

        $body = [
            'content' => 'currentUser try to comment',
        ];

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToUpdate->getId());

        $client->jsonRequest(
            method: 'PATCH',
            uri: $uri,
            parameters: $body
        );

        $this->assertResponseStatusCodeSame(200);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            $body['content'],
            $commentAfterRequest->getContent()
        );
    }

    public function testShouldDenyUserToUpdateCommentWhenNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $commentRepository = $this->getService(CommentRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToUpdate);

        $originalContent = $commentToUpdate->getContent();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToUpdate->getId());

        $client->jsonRequest(
            method: 'PATCH',
            uri: $uri,
            parameters: [
                'content' => 'currentUser try to comment',
            ]
        );

        $this->assertResponseStatusCodeSame(403);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            $originalContent,
            $commentAfterRequest->getContent()
        );
    }

    public function testShouldDenyUserToUpdateCommentWhenIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $commentRepository = $this->getService(CommentRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $commentOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($commentOwner);

        $commentToUpdate = $commentRepository->findOneBy([
            'user' => $commentOwner,
        ]);

        $this->assertNotNull($commentToUpdate);

        $originalContent = $commentToUpdate->getContent();

        $uri = sprintf('%s/comments/%d', ApiTestCase::API_PREFIX, $commentToUpdate->getId());

        $client->jsonRequest(
            method: 'PATCH',
            uri: $uri,
            parameters: [
                'content' => 'currentUser try to comment',
            ]
        );

        $this->assertResponseStatusCodeSame(401);

        $commentAfterRequest = $commentRepository->find($commentToUpdate->getId());

        $this->assertSame(
            $originalContent,
            $commentAfterRequest->getContent()
        );
    }
}
