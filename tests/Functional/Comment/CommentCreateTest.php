<?php

namespace App\Tests\Functional\Comment;

use App\Repository\TicketRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentCreateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideValideCommentData')]
    public function testShouldCreateACommentForATicket(array $data, int $ticketId, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty  = static::getContainer()->get(TicketRepository::class);

        $uri = sprintf('/tickets/%s/comments', $ticketId);
        $client->jsonRequest(method: 'POST', uri: $uri, parameters: $data);

        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $comment = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $comment);

        //Check BDD
        $ticket = $ticketRepositoty->findOneBy(['id' => $ticketId]);
        $ticketComments = $ticket->getComments()->toArray();

        $this->assertCount(4, $ticketComments);
    }

    public static function provideValideCommentData(): \Generator
    {
        yield 'Should add comment with all field filled' => [
            [
                'content' => 'content test 1'
            ],
            1,
            201
        ];
    }

    #[DataProvider('provideInvalidCommentData')]
    public function testShouldNotCreateACommentForATicket(array $data, int $ticketId, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('/tickets/%s/comments', $ticketId);

        $client->jsonRequest(method: 'POST', uri: $uri, parameters: $data);

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideInvalidCommentData(): \Generator
    {
        yield 'Should not add a "comment" without content filled' => [
            [
                'content' => '',
            ],
            1,
            422
        ];
    }
}
