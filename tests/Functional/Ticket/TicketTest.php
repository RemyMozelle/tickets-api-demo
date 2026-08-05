<?php

namespace App\Tests\Functional\Ticket;

use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;

class TicketTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldHaveAllTicketsForWhenUserIsAdmin(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/tickets', ApiTestCase::API_PREFIX);

        $client->jsonRequest('GET', $uri);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(10, $tickets);
    }

    public function testShouldHaveAllTicketsForWhenUserIsAuthenticated(): void
    {
        $client = AuthHelper::createAuthenticatedClient(username: 'user_2_with_2_tickets@gmail.com', password: 'user');

        $uri = sprintf('%s/tickets', ApiTestCase::API_PREFIX);

        $client->jsonRequest('GET', $uri);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(10, $tickets);
    }

    public function testShouldAllowTicketShowWhenUserIsAdmin(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/tickets/%d', ApiTestCase::API_PREFIX, 1);

        $client->jsonRequest('GET', $uri);

        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
        $this->assertResponseIsSuccessful();
    }

    public function testShouldAllowTicketShowWhenUserIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');

        $uri = sprintf('%s/tickets/%d', ApiTestCase::API_PREFIX, 1);

        $client->jsonRequest('GET', $uri);

        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseIsSuccessful();
        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
    }

    public function testShouldAllowTicketShowWhenUserIsNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');

        $uri = sprintf('%s/tickets/%d', ApiTestCase::API_PREFIX, 10);

        $client->jsonRequest('GET', $uri);

        $ticket = ApiHelper::getResponseDecoded($client);

        $this->assertResponseIsSuccessful();
        $this->assertResponseApiField(ApiResponseField::TICKER_READ, $ticket);
    }
}
