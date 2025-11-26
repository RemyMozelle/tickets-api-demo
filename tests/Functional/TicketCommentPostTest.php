<?php

namespace App\Tests;

use App\Repository\TicketRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\GroupContextKeys;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketCommentPostTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessAddTicketCommentData')]
    public function testShouldAddACommentForATicket(array $parameters, array $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty  = static::getContainer()->get(TicketRepository::class);
        $client->jsonRequest(method: 'POST', uri: '/tickets/1/comments', parameters: $data);
        
        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $comment = ApiHelper::getResponseDecoded($client);

        $this->assertSerializedKeys(GroupContextKeys::COMMENT_READ, $comment);

        // Check BDD
        $ticket = $ticketRepositoty->findOneBy(['id' => $parameters['ticket']]);
        $ticketComments = $ticket->getComments()->toArray();

        $this->assertCount(4, $ticketComments);
    }

    public static function provideSuccessAddTicketCommentData(): \Generator
    {
        yield 'Should add ticket with all field filled' => [
            [
                'ticket' => 1,
            ],
            [
                'content' => 'content test 1'
            ],
            201
        ];
    }

    #[DataProvider('provideFailTicketCommentData')]
    public function testShouldNotAddCommentForATicket(array $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $client->jsonRequest(method: 'POST', uri: '/tickets/1/comments', parameters: $data);

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailTicketCommentData(): \Generator
    {
        yield 'Should not add a ticket without content filled' => [
            [
                'content' => ''
            ],
            422
        ];
    }


}
