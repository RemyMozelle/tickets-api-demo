<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\TicketRepository;
use App\Tests\Helper\ApiHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Helper\AuthHelper;

class TicketCreateTest extends WebTestCase
{
    #[DataProvider('provideFailTicketData')]
    public function testShouldFailToAddTicket(array $data, int $expectedStatusCode, array $expectedFields): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $client->jsonRequest(method: 'POST', uri: '/tickets', parameters: $data);
        $response = ApiHelper::getResponseDecoded($client);

        $violations = $response['violations'];

        $actualFields = array_column(
            $violations,
            'propertyPath'
        );

        $this->assertEqualsCanonicalizing($actualFields, $expectedFields);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailTicketData(): \Generator
    {
        yield 'Should fail without field "title"' => [
            [
                'description' => 'Ticket without "Title"',
                'status' => Status::InProgress->value,
                'priority' => Priority::Medium->value,
            ],
            422,
            [
                'title',
            ]
        ];

        yield 'Should fail without field "description"' => [
            [
                'title' => 'Ticket without "Description"',
                'status' => Status::Closed->value,
                'priority' => Priority::High->value,
            ],
            422,
            [
                'description',
            ]
        ];

        yield 'Should fail with invalid "priority" value' => [
            [
                'title' => 'Ticket with invalid "priority" value',
                'description' => 'Ticket with invalid "priority" value',
                'status' => Status::InProgress->value,
                'priority' => 'invalid_priority',
            ],
            422,
            [
                'priority',
            ]
        ];

        yield 'Should fail with invalid "status" value' => [
            [
                'title' => 'Ticket with invalid "status" value',
                'description' => 'Ticket with invalid "status" value',
                'status' => 'invalid_status',
                'priority' => Priority::High->value,
            ],
            422,
            [
                'status',
            ]
        ];

        yield 'Should fail with invalid "status" and "priority" value' => [
            [
                'title' => 'Ticket with invalid "status" and "priority" value',
                'description' => 'Ticket with invalid "status" and "priority" value',
                'status' => 'invalid_status',
                'priority' => 'invalid_priority',
            ],
            422,
            [
                'status',
                'priority',
            ]
        ];
    }

    #[DataProvider('provideSuccessTicketData')]
    public function testShouldSuccessAddTicket(array $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty  = static::getContainer()->get(TicketRepository::class);

        $client->jsonRequest(method: 'POST', uri: '/tickets', parameters: $data);

        $ticket = ApiHelper::getResponseDecoded($client, false);

        $defaultValues = [
            'status' => Status::Open->value, 
            'priority' => Priority::Low->value,
        ];

        $this->assertResponseStatusCodeSame($expectedStatusCode);

        // Check Ticket Json
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
