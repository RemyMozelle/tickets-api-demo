<?php

namespace App\Tests\Functional\Ticket;

use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\AuthHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class TicketDeleteTest extends WebTestCase
{
    public function testShouldDeleteTicketWithCommentsLinked(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $ticketToDelete = $ticketRepository->find(1);

        $commentIds = $ticketToDelete->getComments()->map(fn($c) => $c->getId())->toArray();

        $commentsBefore = $commentRepository->findBy(['id' => $commentIds]);

        $this->assertCount(3, $commentsBefore);

        $client->jsonRequest('DELETE', '/tickets/' . $ticketToDelete->getId());
        $response = $client->getResponse();

        $this->assertSame(204, $response->getStatusCode());

        $this->assertSame('', $response->getContent());

        // check BDD
        $this->assertNull($ticketRepository->find(1));
        $this->assertSame([], $commentRepository->findBy(['id' => $commentIds]));
    }


    public function testShouldDenyTicketDeleteWhenUserIsNotOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient(
            'user_3_with_1_ticket@gmail.com',
            'user'
        );

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $otherUser = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com'
        ]);

        $otherUserTicket = $ticketRepository->findOneBy([
            'user' => $otherUser,
            'title' => 'issue 1',
        ]);

        $this->assertNotNull($otherUserTicket);

        $client->jsonRequest('DELETE', '/tickets/' . $otherUserTicket->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShouldDeleteTicketWhenUserIsOwner(): void
    {
        $client = AuthHelper::createAuthenticatedClient(
            'user_2_with_2_tickets@gmail.com',
            'user'
        );

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $security = static::getContainer()->get(Security::class);

        $currentUser = $security->getUser();

        $criteria = [
            'user' => $currentUser,
            'title' => 'issue 1',
        ];

        $ticket = $ticketRepository->findOneBy(criteria: $criteria);

        $this->assertNotNull($ticket);

        $client->jsonRequest('DELETE', '/tickets/' . $ticket->getId());

        $this->assertResponseStatusCodeSame(204);

        // Check BDD
        $ticketAfterDelete = $ticketRepository->findOneBy($criteria);

        $this->assertNull($ticketAfterDelete);
    }
}
