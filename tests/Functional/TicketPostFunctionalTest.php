<?php

namespace App\Tests;

use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\TicketRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Helper\AuthHelper;

class TicketPostFunctionalTest extends WebTestCase
{
    #[DataProvider('provideFailTicketData')]
    public function testShouldFailToAddTicket($data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $client->jsonRequest(method: 'POST', uri: '/tickets', parameters: $data);

        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    public static function provideFailTicketData(): \Generator
    {
        yield 'Should fail without field "title"' => [
            [
                'description' => 'Ticket without "Title"',
                'status' => Status::InProgress,
                'priority' => Priority::Medium,
            ],
            422
        ];

        yield 'Should fail without field "description"' => [
            [
                'title' => 'Ticket without "Description"',
                'status' => Status::Closed,
                'priority' => Priority::High,
            ],
            422
        ];

        yield 'Should fail with invalid "priority" value' => [
            [
                'title' => 'Ticket without good "priority" value',
                'description' => 'Ticket without good "priority" value',
                'status' => Status::InProgress,
                'priority' => 'invalid_priority', // valeur non autorisée
            ],
            422
        ];

        yield 'Should fail with invalid "status" value' => [
            [
                'title' => 'Ticket without good "status" value',
                'description' => 'Ticket without good "status" value',
                'status' => 'invalid_status',
                'priority' => Priority::High,
            ],
            422
        ];
    }

    #[DataProvider('provideSuccessTicketData')]
    public function testShouldSuccessAddTicket($data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty  = static::getContainer()->get(TicketRepository::class);

        $client->jsonRequest(method: 'POST', uri: '/tickets', parameters: $data);

        $response = $client->getResponse();

        $responseData = json_decode($response->getContent(), false);

        $ticket = $responseData;

        $defaultValues = [
            'status' => Status::Open->value, 
            'priority' => Priority::Low->value,
        ];

        // Check Ticket Json
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertEquals($data['title'], $ticket->title);
        $this->assertEquals($data['description'], $ticket->description);
        foreach ($defaultValues as $field => $default) {
            $expected = $data[$field] ?? $default;
            $this->assertEquals($expected, $ticket->$field);
        }

        // Check BDD
        $this->assertNotNull($ticketRepositoty->findOneBy(['id' => $ticket->id]));
    }

    public static function provideSuccessTicketData(): \Generator
    {
        yield 'Should Add Ticket with all field filled' => [
            [
                'title' => 'Title',
                'description' => 'Ticket without "Title"',
                'status' => Status::InProgress->value,
                'priority' => Priority::Medium->value,
            ],
            201
        ];

        yield 'Should Add Ticket without field "status"' => [
            [
                'title' => 'Title',
                'description' => 'Ticket without "Title"',
                'priority' => Priority::Medium->value,
            ],
            201
        ];

        yield 'Should Add Ticket without field "priority"' => [
            [
                'title' => 'Title',
                'description' => 'Ticket without "Title"',
                'status' => Status::InProgress->value,
            ],
            201
        ];

        yield 'Should Add Ticket without fields "priority" and "status"' => [
            [
                'title' => 'Title',
                'description' => 'Ticket without "Title"',
            ],
            201
        ];
    }
}
