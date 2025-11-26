<?php

namespace App\Tests;

use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketCommentDeleteTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessDeleteTicketCommentData')]
    public function testShouldDeleteCommentForATicket(array $parameters, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $ticketId = $parameters['ticket_id'];
        $commentId = $parameters['comment_id'];

        $ticket = $ticketRepository->find($ticketId);
        $commentToDelete = $commentRepository->find($commentId);

        $this->assertNotNull($commentToDelete);
        $this->assertCount(3, $ticket->getComments()->toArray());

        $uri = sprintf('/tickets/%s/comments/%s', $ticketId, $commentId);

        $client->jsonRequest(method: 'DELETE', uri: $uri);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
        $response = $client->getResponse();

        // check Json
        $this->assertSame('', $response->getContent());

        // check BDD
        $this->assertNull($commentRepository->find($commentId));
        $this->assertCount(2, $ticketRepository->find($ticketId)->getComments());
    }

    public static function provideSuccessDeleteTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            [
                'ticket_id' => 1,
                'comment_id' => 3,
            ],
            204
        ];
    }

    #[DataProvider('provideFailDeleteTicketCommentData')]
    public function testShouldNotDeleteCommentForATicket(array $parameters, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $ticketId = $parameters['ticket_id'];
        $commentId = $parameters['comment_id'];

        $uri = sprintf('/tickets/%s/comments/%s', $ticketId, $commentId);

        $client->jsonRequest(method: 'DELETE', uri: $uri);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailDeleteTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            [
                'ticket_id' => 1,
                'comment_id' => 5,
            ],
            404
        ];
    }
}
