<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Enum\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class TicketListByUserTest extends WebTestCase
{
    #[DataProvider('ticketStatusProvider')]
    public function testUserTicketsCanBeFilteredByStatus($dataParameters, $expectedNbTickets): void
    {
        $status = $dataParameters['query']['status'];

        $client = static::createClient();
        $client->request(method: 'GET', uri: '/users/2/tickets', parameters: [
            'status' => $status
        ]);

        $client->getRequest();

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function ticketStatusProvider(): \Generator
    {
        yield 'User 2 Should have 1 ticket with status "open"' => [
            [
                'query' => [
                    'status' => 'open',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            1
        ];

        yield 'User 2 Should have 0 ticket with status "in_progress"' => [
            [
                'query' => [
                    'status' => 'in_progress',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 0,
                    'current_page' => 1,
                    'total_pages' => 0,
                ],
            ],
            0
        ];

        yield 'User 2 Should have 1 ticket with status "closed"' => [
            [
                'query' => [
                    'status' => 'closed',
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            1
        ];

        yield 'User 2 Should have 1 ticket with status "closed" and 1 ticket with status "open"' => [
            [
                'query' => [
                    'status' => [
                        Status::Closed->value,
                        Status::Open->value,
                    ],
                ],
                'meta' => [
                    'per_page' => 12,
                    'total' => 2,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            2
        ];
    }

    #[DataProvider('ticketShouldHaveALimit')]
    public function testUserTicketsByLimit($dataParameters, $expectedNbTickets): void
    {
        $limit = $dataParameters['query']['limit'];

        $client = static::createClient();
        $client->jsonRequest('GET', '/users/2/tickets?limit=' . $limit);

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function ticketShouldHaveALimit(): \Generator
    {
        yield 'User 2 Should have 2 tickets with limit 2' => [
            [
                'query' => [
                    'limit' => 2,
                ],
                'meta' => [
                    'per_page' => 2,
                    'total' => 2,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            2,
        ];

        yield 'User 2 Should have 1 ticket with limit 1' => [
            [
                'query' => [
                    'limit' => 1,
                ],
                'meta' => [
                    'per_page' => 1,
                    'total' => 2,
                    'current_page' => 1,
                    'total_pages' => 2,
                ],
            ],
            1,
        ];
    }

    #[DataProvider('ticketCombinedFilters')]
    public function testUserTicketsCombinedFilters($dataParameters, $expectedNbTickets): void
    {
        $status = $dataParameters['query']['status'];
        $priority = $dataParameters['query']['priority'];
        $limit = $dataParameters['query']['limit'] ?? 12;

        $client = static::createClient();
        $client->request('GET', '/users/4/tickets', [
            'status' => $status,
            'priority' => $priority,
            'limit' => $limit
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function ticketCombinedFilters(): \Generator
    {
        yield 'User 4 Should have 7 tickets without status and priority values' => [
            [
                'query' => [
                    'status' => "",
                    'priority' => "",
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

        yield 'User 4 Should have 2 tickets status "open" and priority "low"' => [
            [
                'query' => [
                    'status' => Status::Open->value,
                    'priority' => Priority::Low->value,
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

        yield 'User 4 Should have 0 tickets status "open" and priority with value "open, medium"' => [
            [
                'query' => [
                    'status' => Status::Open->value,
                    'priority' => [
                        Priority::Low->value,
                        Priority::Medium->value,
                    ],
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

        yield 'User 4 Should have 5 tickets status with values ("open", "closed") and priority with value ("open, medium")' => [
            [
                'query' => [
                    'status' => [
                        Status::Open->value,
                        Status::Closed->value,
                    ],
                    'priority' => [
                        Priority::Low->value,
                        Priority::Medium->value,
                    ],
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

        yield 'User 4 Should have 5 ticket status with values ("open", "closed") and priority with value ("open, medium") with limit to 1' => [
            [
                'query' => [
                    'status' => [
                        Status::Open->value,
                        Status::Closed->value,
                    ],
                    'priority' => [
                        Priority::Low->value,
                        Priority::Medium->value,
                    ],
                    'limit' => 1
                ],
                'meta' => [
                    'per_page' => 1,
                    'total' => 5,
                    'current_page' => 1,
                    'total_pages' => 5,
                ],
            ],
            1,
        ];
    }
}
