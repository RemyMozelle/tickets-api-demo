<?php

namespace App\Tests\Functional\Ticket;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
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

    public function testShouldHaveTicketDetail()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1');

        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
        $this->assertResponseIsSuccessful();
        $this->assertEquals($ticket['id'], 1);
    }
}
