<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Status;
use App\Tests\Helper\ApiHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketListByUserTest extends WebTestCase
{
    /**
     * @param list<string> $expectedTicketTitles
     */
    #[DataProvider('provideFilterUserData')]
    public function testShouldReturnAllTicketsForGivenUser(int $userId, array $expectedTicketTitles): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: sprintf('/users/%d/tickets', $userId));

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];
        $nbTickets = count($expectedTicketTitles);

        $this->assertEqualsCanonicalizing($expectedTicketTitles, array_column($tickets, 'title'));

        $this->assertResponseIsSuccessful();
        $this->assertCount($nbTickets, $tickets);
    }

    public static function provideFilterUserData(): \Generator
    {
        $cases = [
            [
                'user_id' => 2,
                'ticket_titles' => [
                    'Issue 1',
                    'Issue 2',
                ]
            ],
            [
                'user_id' => 3,
                'ticket_titles' => [
                    'Issue 3',
                ]
            ],
            [
                'user_id' => 4,
                'ticket_titles' => [
                    'Issue 4',
                    'Issue 5',
                    'Issue 6',
                    'Issue 7',
                    'Issue 8',
                    'Issue 9',
                    'Issue 10',
                ]
            ]
        ];

        foreach ($cases as $key => $case) {
            yield sprintf(
                'case n°%d should return %d tickets with user [%s]',
                $key + 1,
                count($case['ticket_titles']),
                $case['user_id'],
            ) => [
                $case['user_id'],
                $case['ticket_titles'],
            ];
        }
    }

    public function testShouldReturnNotFoundWhenUserDoesNotExist(): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/users/999/tickets');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShouldReturnEmptyCollectionWhenUserHasNoTickets(): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/users/7/tickets');

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $tickets);
    }

    public function testShouldReturnFilteredTicketsForGivenUser(): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/users/4/tickets', parameters: [
            'status' => Status::Open->value,
        ]);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];

        $this->assertEqualsCanonicalizing([
            'Issue 5',
            'Issue 6',
            'Issue 8',
            'Issue 9',
        ], array_column($tickets, 'title'));

        $this->assertResponseIsSuccessful();
        $this->assertCount(4, $tickets);
    }

    public function testShouldPaginateTicketsForGivenUser(): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: '/users/4/tickets', parameters: [
            'status' => Status::Open->value,
            'limit' => 2,
            'page' => 2,
        ]);

        $response = ApiHelper::getResponseDecoded($client);

        $currentPaginateTickets = $response['data'];
        $meta = $response['meta'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $currentPaginateTickets);
        $this->assertSame(
            [
                "total" => 4,
                "per_page" => 2,
                "current_page" => 2,
                "total_pages" => 2,
            ],
            $meta
        );
    }
}
