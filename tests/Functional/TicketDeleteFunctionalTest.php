<?php

namespace App\Tests;

use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Tests\Helper\AuthHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketDeleteFunctionalTest extends WebTestCase
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
}
