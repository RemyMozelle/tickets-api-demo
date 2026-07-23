<?php

namespace App\Tests\Functional\Ticket;

use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldHaveAllTickets(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets');

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(10, $tickets);
    }

    public function testShouldAllowTicketShowWhenUserIsAdmin()
    {
        $client = AuthHelper::createAuthenticatedClient();
        $client->jsonRequest('GET', '/tickets/1');

        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
        $this->assertResponseIsSuccessful();
    }

    public function testShouldAllowTicketShowWhenUserIsOwner()
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');

        $client->jsonRequest('GET', '/tickets/1');
        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseIsSuccessful();
        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
    }

    public function testShouldDenyTicketShowWhenUserIsNotOwner()
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');
        $client->jsonRequest('GET', '/tickets/10');

        $this->assertResponseStatusCodeSame(403);
    }
}
