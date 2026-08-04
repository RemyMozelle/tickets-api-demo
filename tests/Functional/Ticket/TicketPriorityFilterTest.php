<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class TicketPriorityFilterTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, string> $queryParameters
     */
    #[DataProvider('providePriorityFilterData')]
    public function testShouldFilterTicketsByPriority(array $queryParameters, int $expectedNbTickets): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/tickets', ApiTestCase::API_PREFIX);

        $client->request(method: 'GET', uri: $uri, parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
    }

    public static function providePriorityFilterData(): \Generator
    {
        $cases = [
            'high' => 4,
            'medium' => 2,
            'low' => 4,
        ];

        foreach ($cases as $priority => $expectedNbTickets) {
            yield sprintf('should return %d tickets with property "%s"', $expectedNbTickets, $priority) => [
                [
                    'priority' => $priority,
                ],
                $expectedNbTickets,
            ];
        }
    }

    /**
     * @param array<string, array<int, string>> $queryParameters
     */
    #[DataProvider('provideMultiplePriorityFilterData')]
    public function testShouldFilterTicketsByMultiplePriorities(array $queryParameters, int $expectedNbTickets): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/tickets', ApiTestCase::API_PREFIX);

        $client->request(method: 'GET', uri: $uri, parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
    }

    public static function provideMultiplePriorityFilterData(): \Generator
    {
        $cases = [
            'high - medium' => [
                'query' => [
                    'priority' => [
                        Priority::High->value,
                        Priority::Medium->value,
                    ],
                ],
                'expectedNbTickets' => 6,
            ],
            'medium - low' => [
                'query' => [
                    'priority' => [
                        Priority::Medium->value,
                        Priority::Low->value,
                    ],
                ],
                'expectedNbTickets' => 6,
            ],
            'low - high' => [
                'query' => [
                    'priority' => [
                        Priority::Low->value,
                        Priority::High->value,
                    ],
                ],
                'expectedNbTickets' => 8,
            ],
        ];

        foreach ($cases as $case) {
            $nbTickets = $case['expectedNbTickets'];
            yield sprintf(
                'should return %d tickets with priority [%s]',
                $nbTickets,
                implode(', ', $case['query']['priority'])
            ) => [
                $case['query'],
                $nbTickets,
            ];
        }
    }
}
