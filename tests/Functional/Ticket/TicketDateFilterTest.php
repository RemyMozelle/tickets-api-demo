<?php

namespace App\Tests\Functional\Ticket;

use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class TicketDateFilterTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, string> $queryParameters
     * @param array<string, list<string>> $expected
     */
    #[DataProvider('ticketWithDateParameters')]
    public function testShouldHaveTicketWithSpecificsDates(array $queryParameters, array $expected): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/tickets', ApiTestCase::API_PREFIX);

        $client->request(
            method: 'GET',
            uri: $uri,
            parameters: $queryParameters
        );

        $response = ApiHelper::getResponseDecoded($client);
        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertEqualsCanonicalizing($expected['ticket_titles'], array_column($tickets, 'title'));
    }

    public static function ticketWithDateParameters(): \Generator
    {
        yield 'Should have 1 tickets with date 2025-01-01 to 2025-01-02' => [
            [
                'start_date' => '2025-01-01',
                'end_date' => '2025-01-02',
            ],
            [
                'ticket_titles' => ['Issue 5'],
            ],
        ];

        yield 'Should have 10 tickets with date 2023-09-30' => [
            [
                'start_date' => '2023-09-30',
            ],
            [
                'ticket_titles' => [
                    'Issue 1',
                    'Issue 2',
                    'Issue 3',
                    'Issue 4',
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                    'Issue 10',
                ],
            ],
        ];

        yield 'Should have 3 tickets with date 2025-01-01 to 2025-04-12' => [
            [
                'start_date' => '2025-01-01',
                'end_date' => '2025-04-12',
            ],
            [
                'ticket_titles' => [
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                ],
            ],
        ];

        yield 'Should have 1 ticket with date 2025-01-01 07:00:00 to 2025-01-01 23:59:59' => [
            [
                'start_date' => '2025-01-01',
                'start_time' => '07:00:00',
                'end_time' => '23:59:59',
            ],
            [
                'ticket_titles' => [
                    'Issue 5',
                ],
            ],
        ];

        yield 'Should have 0 ticket with date 2025-01-01 08:00:00 to 2025-01-01 23:59:59' => [
            [
                'start_date' => '2025-01-01',
                'start_time' => '08:00:00',
                'end_time' => '23:59:59',
            ],
            [
                'ticket_titles' => [],
            ],
        ];

        yield 'Should have 5 tickets with date 2025-01-01 07:00 to 2025-05-01 13:00:00' => [
            [
                'start_date' => '2025-01-01',
                'end_date' => '2025-05-01',
                'start_time' => '07:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'ticket_titles' => [
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                ],
            ],
        ];

        yield 'Should have 1 ticket with date 2025-01-01 07:00' => [
            [
                'start_date' => '2025-01-01',
                'end_time' => '07:00:00',
            ],
            [
                'ticket_titles' => ['Issue 5'],
            ],
        ];

        yield 'Should have 8 tickets with date 2024-10-02' => [
            [
                'start_date' => '2024-10-02',
            ],
            [
                'ticket_titles' => [
                    'Issue 3',
                    'Issue 4',
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                    'Issue 10',
                ],
            ],
        ];

        yield 'Should have 7 tickets with date 2024-10-02 to 2025-05-01' => [
            [
                'start_date' => '2024-10-02',
                'end_date' => '2025-05-01',
            ],
            [
                'ticket_titles' => [
                    'Issue 3',
                    'Issue 4',
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                ],
            ],
        ];

        yield 'Should have 0 tickets with date 2025-01-01 to 2025-01-01 06:00' => [
            [
                'start_date' => '2025-01-01',
                'end_time' => '06:00:00',
            ],
            [
                'ticket_titles' => [],
            ],
        ];

        yield 'Should have 1 tickets with date 2025-04-12 10:00:00 to 2025-04-12 13:00:00' => [
            [
                'start_date' => '2025-04-12',
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'ticket_titles' => [
                    'Issue 7',
                ],
            ],
        ];

        yield 'Should have 3 tickets with date 2025-04-12 12:00:00 to 2025-05-01 11:00:00' => [
            [
                'start_date' => '2025-04-12',
                'end_date' => '2025-05-01',
                'start_time' => '12:00:00',
                'end_time' => '11:00:00',
            ],
            [
                'ticket_titles' => [
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                ],
            ],
        ];

        yield 'Should have 3 ticket with date 2025-05-01 10:00:00' => [
            [
                'start_date' => '2025-05-01',
                'start_time' => '10:00:00',
            ],
            [
                'ticket_titles' => [
                    'Issue 8',
                    'Issue 9',
                    'Issue 10',
                ],
            ],
        ];

        yield 'Should have 2 ticket with date 2025-05-01 11:00:00' => [
            [
                'start_date' => '2025-05-01',
                'start_time' => '11:00:00',
            ],
            [
                'ticket_titles' => [
                    'Issue 9',
                    'Issue 10',
                ],
            ],
        ];
    }
}
