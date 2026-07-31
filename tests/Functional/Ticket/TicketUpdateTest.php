<?php

namespace App\Tests\Functional\Ticket;

use App\Entity\Ticket;
use App\Enum\Priority;
use App\Enum\Status;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Tests\Factory\TicketFactory;
use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\SecurityBundle\Security;

class TicketUpdateTest extends ApiTestCase
{
    // #[DataProvider('provideTicketData')]
    // public function testShouldUpdateTicket(int $ticketId, array $ticketValues, $post, int $expectedStatusCode): void
    // {
    //     $client = AuthHelper::createAuthenticatedClient();
    //     $ticketRepositoty = static::getContainer()->get(TicketRepository::class);

    //     $oldTicket = $ticketRepositoty->find($ticketId);
    //     // check BDD old value
    //     $this->assertEquals($ticketValues['before_update']['status'], $oldTicket->getStatus());
    //     $this->assertEquals($ticketValues['before_update']['priority'], $oldTicket->getPriority());

    //     $client->jsonRequest(method: 'PATCH', uri: '/tickets/' . $oldTicket->getId(), parameters: $post['send_to_api']);

    //     $response = $client->getResponse();

    //     $responseData = json_decode($response->getContent(), false);
    //     $ticketFromResponse = $responseData;

    //     $oldTicket = $ticketRepositoty->findOneBy(['id' => $oldTicket->getId()]);

    //     $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    //     // check BDD
    //     $this->assertEquals($ticketValues['after_update']['status'], $oldTicket->getStatus());
    //     $this->assertEquals($ticketValues['after_update']['priority'], $oldTicket->getPriority());
    //     // check Json
    //     $this->assertEquals($ticketValues['after_update']['status']->value, $ticketFromResponse->status);
    //     $this->assertEquals($ticketValues['after_update']['priority']->value, $ticketFromResponse->priority);
    // }

    // public static function provideTicketData(): \Generator
    // {
    //     yield 'Should correctly update ticket with id "1" with "status" : "open" to "closed", "priority" : "High" to "Low"' => [
    //         1,
    //         [
    //             'before_update' => [
    //                 'status' => Status::Open,
    //                 'priority' => Priority::High,
    //             ],
    //             'after_update' => [
    //                'status' => Status::Closed,
    //                'priority' => Priority::Low,
    //            ]
    //         ],
    //         [
    //             'send_to_api' => [
    //                 'status' => Status::Closed,
    //                 'priority' => Priority::Low,
    //             ]
    //         ],
    //         200
    //     ];

    //     yield 'Should correctly update ticket with id "1" with "status" : "open" to "closed"' => [
    //         1,
    //         [
    //             'before_update' => [
    //                 'status' => Status::Open,
    //                 'priority' => Priority::High,
    //             ],
    //             'after_update' => [
    //                 'status' => Status::Closed,
    //                 'priority' => Priority::High,
    //             ]
    //         ],
    //         [
    //             'send_to_api' => [
    //                 'status' => Status::Closed,
    //             ]
    //         ],
    //         200
    //     ];
    // }

    /**
     * @param array<string, array<string, mixed>> $expected
     * @param array<string, mixed> $body
     */
    #[DataProvider('provideTicketData')]
    public function testShouldUpdateTicket(Ticket $ticketBeforeExist, array $expected, array $body): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $ticketRepository = $this->getService(TicketRepository::class);
        $userRepository = $this->getService(UserRepository::class);
        $manager = $this->getService(EntityManagerInterface::class);

        $ticketBeforeExist->setUser($userRepository->findOneBy([
            'email' => 'admin_1@gmail.com',
        ]));

        $manager->persist($ticketBeforeExist);
        $manager->flush();

        $ticket = $ticketRepository->findOneBy([
            'title' => $ticketBeforeExist->getTitle(),
        ]);
        $expected = $expected['expected'];

        $client->jsonRequest(method: 'PATCH', uri: '/tickets/' . $ticket->getId(), parameters: $body);

        $this->assertResponseStatusCodeSame(200);

        // Check BDD
        $ticketAfterUpdate = $ticketRepository->findOneBy([
            'title' => $ticket->getTitle(),
        ]);
        $this->assertSame($expected['title'], $ticketAfterUpdate->getTitle());
        $this->assertSame($expected['description'], $ticketAfterUpdate->getDescription());
        $this->assertEquals($expected['status'], $ticketAfterUpdate->getStatus());
        $this->assertEquals($expected['priority'], $ticketAfterUpdate->getPriority());
        $this->assertEquals($expected['created_at'], $ticketAfterUpdate->getCreatedAt());
        $this->assertEquals($expected['updated_at'], $ticketAfterUpdate->getUpdatedAt());

        // Check JSON
        $ticketFromResponse = ApiHelper::getResponseDecoded($client, false);
        $this->assertSame($expected['title'], $ticketFromResponse->title);
        $this->assertSame($expected['description'], $ticketFromResponse->description);
        $this->assertEquals($expected['status']->value, $ticketFromResponse->status);
        $this->assertEquals($expected['priority']->value, $ticketFromResponse->priority);
        $this->assertEquals($expected['created_at'], new DateTimeImmutable($ticketFromResponse->created_at));
        $this->assertEquals($expected['updated_at'], new DateTimeImmutable($ticketFromResponse->updated_at));
    }

    public static function provideTicketData(): \Generator
    {
        $ticket = TicketFactory::make([
            'title' => 'issue de test',
            'description' => 'issue de test description',
            'status' => Status::Open,
            'priority' => Priority::High,
            'created_at' => new DateTimeImmutable('2024-09-30 12:00:00'),
            'updated_at' => new DateTimeImmutable('2025-01-01 11:00:00'),
        ]);

        yield 'Should correctly update ticket with id "1" with "status" : "open" to "closed", "priority" : "High" to "Low"' => [
            $ticket,
            [
                'expected' => [
                    'title' => $ticket->getTitle(),
                    'description' => $ticket->getDescription(),
                    'status' => Status::Closed,
                    'priority' => Priority::Low,
                    'created_at' => $ticket->getCreatedAt(),
                    'updated_at' => $ticket->getUpdatedAt(),
                ],
            ],
            [
                'status' => Status::Closed,
                'priority' => Priority::Low,
            ],
        ];

        /* TODO: Add test for updated_at field
         * $ticket = TicketFactory::make([
         * 'title' => 'issue de test',
         * 'description' => 'issue de test description',
         * 'status' => Status::Open,
         * 'priority' => Priority::High,
         * 'created_at' => new DateTimeImmutable("2024-09-30 12:00:00"),
         * 'updated_at' => new DateTimeImmutable("2025-01-01 11:00:00")
         * ]);
         * yield 'Should only update field "updated_at"' => [
         * $ticket,
         * [
         * 'expected' => [
         * 'title' => $ticket->getTitle(),
         * 'description' => $ticket->getDescription(),
         * 'status' => Status::Open,
         * 'priority' => Priority::High,
         * 'created_at' => $ticket->getCreatedAt(),
         * 'updated_at' => new DateTimeImmutable("2026-01-01 14:00:00"),
         * ],
         * ],
         * [
         * 'updated_at' => "2026-01-01 14:00:00"
         * ],
         * ];
         */
    }

    public function testShouldDenyTicketUpdateWhenUserIsNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient(
            'user_3_with_1_ticket@gmail.com',
            'user'
        );

        $ticketRepository = $this->getService(TicketRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $otherUser = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $otherUserTicket = $ticketRepository->findOneBy([
            'user' => $otherUser,
            'title' => 'issue 1',
        ]);

        $this->assertNotNull($otherUserTicket);

        $client->jsonRequest(
            method: 'PATCH',
            uri: '/tickets/' . $otherUserTicket->getId(),
            parameters: [
                'title' => 'test',
            ]
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShouldUpdateTicketWhenUserIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient(
            'user_2_with_2_tickets@gmail.com',
            'user'
        );

        $ticketRepository = $this->getService(TicketRepository::class);
        $security = $this->getService(Security::class);

        $currentUser = $security->getUser();

        $ticket = $ticketRepository->findOneBy([
            'user' => $currentUser,
            'title' => 'issue 1',
        ]);

        $this->assertNotNull($ticket);

        $body = [
            'title' => 'issue 1 tested',
        ];

        $client->jsonRequest(
            method: 'PATCH',
            uri: '/tickets/' . $ticket->getId(),
            parameters: $body
        );

        $this->assertResponseStatusCodeSame(200);

        // Check BDD
        $ticketAfterUpdate = $ticketRepository->findOneBy($body);

        $this->assertSame($body['title'], $ticketAfterUpdate->getTitle());
    }
}
