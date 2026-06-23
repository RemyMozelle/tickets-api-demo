<?php

namespace App\Tests\Functional\Comment;

use App\Repository\CommentRepository;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentDeleteTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessDeleteTicketCommentData')]
    public function testShouldDeleteCommentForATicket(array $parameters, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $commentId = $parameters['comment_id'];

        $commentToDelete = $commentRepository->find($commentId);

        $this->assertNotNull($commentToDelete);

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'DELETE', uri: $uri);


        $this->assertResponseStatusCodeSame($expectedStatusCode);
        $response = $client->getResponse();

        // check Json
        $this->assertSame('', $response->getContent());

        // check BDD
        $this->assertNull($commentRepository->find($commentId));
    }

    public static function provideSuccessDeleteTicketCommentData(): \Generator
    {
        yield 'Should delete a comment' => [
            [
                'comment_id' => 3,
            ],
            204
        ];
    }

    #[DataProvider('provideFailDeleteTicketCommentData')]
    public function testShouldNotDeleteCommentForATicket(array $parameters, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentId = $parameters['comment_id'];

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'DELETE', uri: $uri);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailDeleteTicketCommentData(): \Generator
    {
        yield 'Should not delete a comment who do not exist' => [
            [
                'comment_id' => 100000000000,
            ],
            404
        ];

        yield 'Should not delete with an ID empty' => [
            [
                'comment_id' => '',
            ],
            404
        ];

        yield 'Should not delete with an ID to NULL' => [
            [
                'comment_id' => NULL,
            ],
            404
        ];
    }
}
