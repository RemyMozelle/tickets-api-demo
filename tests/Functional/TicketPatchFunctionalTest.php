<?php

namespace App\Tests;

use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\TicketRepository;
use App\Tests\Helper\AuthHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketPatchFunctionalTest extends WebTestCase
{
    #[DataProvider('provideTicketData')]
    public function testShouldUpdateTicket($oldTicketValues, $updatedTicketValues, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty = static::getContainer()->get(TicketRepository::class);

        $oldTicket = $ticketRepositoty->findOneBy($oldTicketValues['before_update']);

        $client->jsonRequest(method: 'PATCH', uri: '/tickets/' . $oldTicket->getId(), parameters: $updatedTicketValues['after_update']);

        $response = $client->getResponse();

        $responseData = json_decode($response->getContent(), false);
        $ticketFromResponse = $responseData;

        $oldTicket = $ticketRepositoty->findOneBy(['id' => $oldTicket->getId()]);

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        // check BDD
        $this->assertEquals($updatedTicketValues['after_update']['status'], $oldTicket->getStatus());
        $this->assertEquals($updatedTicketValues['after_update']['priority'], $oldTicket->getPriority());
        // check Json
        $this->assertEquals($updatedTicketValues['after_update']['status']->value, $ticketFromResponse->status);
        $this->assertEquals($updatedTicketValues['after_update']['priority']->value, $ticketFromResponse->priority);
    }

    public static function provideTicketData(): \Generator
    {
        yield 'Should correctly update ticket "status" : "open" to "closed", "priority" : "High" to "Low"' => [
            [
                'before_update' => [
                    'status' => Status::Open,
                    'priority' => Priority::High,
                ]
            ],
            [
                'after_update' => [
                    'status' => Status::Closed,
                    'priority' => Priority::Low,
                ]
            ],
            200
        ];
    }
}
