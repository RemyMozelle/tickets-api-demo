<?php

namespace App\Tests\Functional\Comment;

use App\Repository\CommentRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
}
