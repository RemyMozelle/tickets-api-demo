<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\TicketRepository;
use App\Tests\Helper\AuthHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketUpdateTest extends WebTestCase
{
    #[DataProvider('provideTicketData')]
    public function testShouldUpdateTicket($ticketId, $ticketValues, $post, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty = static::getContainer()->get(TicketRepository::class);

        $oldTicket = $ticketRepositoty->find($ticketId);
        // check BDD old value
        $this->assertEquals($ticketValues['before_update']['status'], $oldTicket->getStatus());
        $this->assertEquals($ticketValues['before_update']['priority'], $oldTicket->getPriority());

        $client->jsonRequest(method: 'PATCH', uri: '/tickets/' . $oldTicket->getId(), parameters: $post['send_to_api']);

        $response = $client->getResponse();

        $responseData = json_decode($response->getContent(), false);
        $ticketFromResponse = $responseData;

        $oldTicket = $ticketRepositoty->findOneBy(['id' => $oldTicket->getId()]);

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        // check BDD
        $this->assertEquals($ticketValues['after_update']['status'], $oldTicket->getStatus());
        $this->assertEquals($ticketValues['after_update']['priority'], $oldTicket->getPriority());
        // check Json
        $this->assertEquals($ticketValues['after_update']['status']->value, $ticketFromResponse->status);
        $this->assertEquals($ticketValues['after_update']['priority']->value, $ticketFromResponse->priority);
    }

    public static function provideTicketData(): \Generator
    {
        yield 'Should correctly update ticket with id "1" with "status" : "open" to "closed", "priority" : "High" to "Low"' => [
            1,
            [
                'before_update' => [
                    'status' => Status::Open,
                    'priority' => Priority::High,
                ],
                'after_update' => [
                   'status' => Status::Closed,
                   'priority' => Priority::Low,
               ]
            ],
            [
                'send_to_api' => [
                    'status' => Status::Closed,
                    'priority' => Priority::Low,
                ]
            ],
            200
        ];

        yield 'Should correctly update ticket with id "1" with "status" : "open" to "closed"' => [
            1,
            [
                'before_update' => [
                    'status' => Status::Open,
                    'priority' => Priority::High,
                ],
                'after_update' => [
                    'status' => Status::Closed,
                    'priority' => Priority::High,
                ]
            ],
            [
                'send_to_api' => [
                    'status' => Status::Closed,
                ]
            ],
            200
        ];
    }
}
