<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Status;
use App\Tests\Helper\ApiHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketStatusFilterTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideStatusFilterData')]
    public function testShouldFilterTicketsByStatus(array $queryParameters, int $expectedNbTickets): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/tickets', parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
    }

    public static function provideStatusFilterData(): \Generator
    {
        $cases = [
            'open' => 5,
            'closed' => 4,
            'in_progress' => 1,
        ];

        foreach ($cases as $status => $expectedNbTickets) {
            yield sprintf('should return %d tickets with status "%s"', $expectedNbTickets, $status) => [
                [
                    'status' => $status
                ],
                $expectedNbTickets,
            ];
        }
    }

    #[DataProvider('provideMultipleStatusFilterData')]
    public function testShouldFilterTicketsByMultipleStatuses(array $queryParameters, int $expectedNbTickets): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/tickets', parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount($expectedNbTickets, $tickets);
    }

    public static function provideMultipleStatusFilterData(): \Generator
    {
        $cases = [
            'open - closed' => [
                'query' => [
                    'status' => [
                        Status::Open->value,
                        Status::Closed->value
                    ]
                ],
                'expectedNbTickets' => 9
            ],
            // 'closed - in progress' => [
            //     'query' => [
            //         'status' => [
            //             Status::Closed->value,
            //             Status::InProgress->value
            //         ]
            //     ],
            //     'expectedNbTickets' => 5
            // ],
            // 'in_progress - open' => [
            //     'query' => [
            //         'status' => [
            //             Status::InProgress->value,
            //             Status::Open->value
            //         ]
            //     ],
            //     'expectedNbTickets' => 6
            // ]
        ];

        foreach ($cases as $case) {
            $nbTickets = $case['expectedNbTickets'];
            yield sprintf(
                'should return %d tickets with status [%s]',
                $nbTickets,
                implode(', ', $case['query']['status'])
            ) => [
                $case['query'],
                $nbTickets,
            ];
        }
    }
}
