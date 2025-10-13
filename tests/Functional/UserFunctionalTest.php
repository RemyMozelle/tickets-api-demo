<?php

namespace App\Functional\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserFunctionalTest extends WebTestCase
{
    #[DataProvider('ticketStatusProvider')]
    public function testUserTicketsByStatus($dataParameters, $expectedNbTickets): void
    {
        $status = $dataParameters['query']['status'];

        $client = static::createClient();
        $client->jsonRequest('GET', '/users/2/tickets?status=' . $status);

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
        yield 'User 2 Should have 1 ticket with status open' => [
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

        yield 'User 2 Should have 0 ticket with status in_progress' => [
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

        yield 'User 2 Should have 1 ticket with status closed' => [
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

    #[DataProvider('commentsShouldHaveALimit')]
    public function testUserCommentsByLimit($dataParameters, $expectedNbComments): void
    {
        $limit = $dataParameters['query']['limit'];

        $client = static::createClient();
        $client->jsonRequest('GET', '/users/2/comments?limit=' . $limit);

        $data = json_decode($client->getResponse()->getContent(), true);

        $comments = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();

        $this->assertCount($expectedNbComments, $comments);
        $this->assertEquals($expectedMeta, $meta);
    }

    public function testUserDetail()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/users/1');

        $data = json_decode($client->getResponse()->getContent());

        $user = $data->data;

        $this->assertResponseIsSuccessful();

        $this->assertIsObject($user);
        $this->assertEquals($user->id, 1);
    }

    public static function commentsShouldHaveALimit(): \Generator
    {
        yield 'User 2 Should have 2 comments with limit 2' => [
            [
                'query' => [
                    'limit' => 2,
                ],
                'meta' => [
                    'per_page' => 2,
                    'total' => 4,
                    'current_page' => 1,
                    'total_pages' => 2,
                ],
            ],
            2
        ];

        yield 'User 2 Should have 1 comment with limit 1' => [
            [
                'query' => [
                    'limit' => 1,
                ],
                'meta' => [
                    'per_page' => 1,
                    'total' => 4,
                    'current_page' => 1,
                    'total_pages' => 4,
                ],
            ],
            1
        ];
    }
}
