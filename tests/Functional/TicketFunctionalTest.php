<?php

namespace App\Tests;

use App\Enum\Priority;
use App\Enum\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketFunctionalTest extends WebTestCase
{
    public function testShouldHaveAllTickets(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets');

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = [
            'per_page' => 12,
            'total' => 8,
            'current_page' => 1,
            'total_pages' => 1,
        ];

        $this->assertResponseIsSuccessful();
        $this->assertCount(8, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    #[DataProvider('TicketWithLimitAndStatus')]
    public function testShouldHaveAllTicketsWithSpecificsParameters($dataParameters, $expectedNbTickets): void
    {
        $limit = $dataParameters['query']['limit'];
        $status = $dataParameters['query']['status'];
        $priority = $dataParameters['query']['priority'] ?? null;

        $client = static::createClient();
        $client->request(method: 'GET', uri: '/tickets', parameters: [
            'limit' => $limit,
            'status' => $status,
            'priority' => $priority,
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function TicketWithLimitAndStatus(): \Generator
    {
        yield 'Should have 2 tickets with status open and closed' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => [
                        Status::Open->value,
                        Status::Closed->value,
                    ]
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 7,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            7,
        ];


        yield 'Should have 4 ticket with status open' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::Open->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 4,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            4,
        ];

        yield 'Should have 3 ticket with status closed' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::Closed->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 3,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            3,
        ];

        yield 'Should have 1 ticket with status on_progress' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::InProgress->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            1,
        ];

        yield 'Should have 0 ticket with status on_progress and priority to medium' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::InProgress->value,
                    'priority' => Priority::Medium->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 0,
                    'current_page' => 1,
                    'total_pages' => 0,
                ],
            ],
            0,
        ];

        yield 'Should have 2 ticket with status open and priority to medium' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::Open->value,
                    'priority' => Priority::Medium->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 2,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            2,
        ];

        yield 'Should have 6 ticket with priority to low and High' => [
            [
                'query' => [
                    'limit' => 12,
                    'priority' => [
                        Priority::Low->value,
                        Priority::High->value,
                    ]
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 6,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            6,
        ];
    }

    public function testTicketDetail()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1');

        $data = json_decode($client->getResponse()->getContent());

        $ticket = $data->data;

        $this->assertResponseIsSuccessful();
        $this->assertEquals($ticket->id, 1);
    }

    public function testTicketCommentForUser()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $tickets);
    }
}
