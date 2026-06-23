<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Enum\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketTest extends WebTestCase
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
            'total' => 10,
            'current_page' => 1,
            'total_pages' => 1,
        ];

        $this->assertResponseIsSuccessful();
        $this->assertCount(10, $tickets);
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
                    'total' => 9,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            9,
        ];


        yield 'Should have 4 ticket with status open' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::Open->value,
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 5,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            5,
        ];

        yield 'Should have 3 ticket with status closed' => [
            [
                'query' => [
                    'limit' => 12,
                    'status' => Status::Closed->value,
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

        yield 'Should have 4 ticket with status open and priority to medium' => [
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
                    'total' => 8,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            8,
        ];
    }

    public function testShouldHaveTicketDetail()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1');

        $data = json_decode($client->getResponse()->getContent());

        $ticket = $data;

        $this->assertResponseIsSuccessful();
        $this->assertEquals($ticket->id, 1);
    }

    #[DataProvider('ticketWithDateParameters')]
    public function testShouldHaveTicketWithSpecificsDates($dataParameters, $expectedNbTickets)
    {
        $startDate = $dataParameters['query']['start_date'];
        $endDate = $dataParameters['query']['end_date'];
        $startTime = $dataParameters['query']['start_time'];
        $endTime = $dataParameters['query']['end_time'];

        $parameters = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $client = static::createClient();
        $client->request(
            method: 'GET',
            uri: '/tickets',
            parameters: $parameters
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function ticketWithDateParameters(): \Generator
    {
        yield 'Should have 1 tickets with date 2025-01-01 to 2025-01-02' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-01-02',
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

        yield 'Should have 10 tickets with date 2023-09-30' => [
            [
                'query' => [
                    'start_date' => '2023-09-30',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 10,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            10,
        ];

        yield 'Should have 3 tickets with date 2025-01-01 to 2025-04-12' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-04-12',
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

        yield 'Should have 1 ticket with date 2025-01-01 07:00:00 to 2025-01-01 23:59:59' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'start_time' => '07:00:00',
                    'end_time' => '23:59:59',
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

        yield 'Should have 0 ticket with date 2025-01-01 08:00:00 to 2025-01-01 23:59:59' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'start_time' => '08:00:00',
                    'end_time' => '23:59:59',
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

        yield 'Should have 5 tickets with date 2025-01-01 07:00 to 2025-05-01 13:00:00' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-05-01',
                    'start_time' => '07:00:00',
                    'end_time' => '13:00:00',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 5,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            5,
        ];

        yield 'Should have 1 ticket with date 2025-01-01 07:00' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'end_time' => '07:00:00',
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

        yield 'Should have 8 tickets with date 2024-10-02' => [
            [
                'query' => [
                    'start_date' => '2024-10-02',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 8,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            8,
        ];

        yield 'Should have 7 tickets with date 2024-10-02 to 2025-05-01' => [
            [
                'query' => [
                    'start_date' => '2024-10-02',
                    'end_date' => '2025-05-01'
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

        yield 'Should have 0 tickets with date 2025-01-01 to 2025-01-01 06:00' => [
            [
                'query' => [
                    'start_date' => '2025-01-01',
                    'end_time' => '06:00:00',
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

        yield 'Should have 1 tickets with date 2025-04-12 10:00:00 to 2025-04-12 13:00:00' => [
            [
                'query' => [
                    'start_date' => '2025-04-12',
                    'start_time' => '10:00:00',
                    'end_time' => '13:00:00',
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

        yield 'Should have 3 tickets with date 2025-04-12 12:00:00 to 2025-05-01 11:00:00' => [
            [
                'query' => [
                    'start_date' => '2025-04-12',
                    'end_date' => '2025-05-01',
                    'start_time' => '12:00:00',
                    'end_time' => '11:00:00',
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

        yield 'Should have 3 ticket with date 2025-05-01 10:00:00' => [
            [
                'query' => [
                    'start_date' => '2025-05-01',
                    'start_time' => '10:00:00',
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

        yield 'Should have 2 ticket with date 2025-05-01 11:00:00' => [
            [
                'query' => [
                    'start_date' => '2025-05-01',
                    'start_time' => '11:00:00',
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
    }
}
