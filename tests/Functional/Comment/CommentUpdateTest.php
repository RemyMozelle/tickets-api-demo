<?php

namespace App\Tests\Functional\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
}
