<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketCommentFunctionalTest extends WebTestCase
{
    public function testShouldHaveCommentsForATicket()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $data = json_decode($client->getResponse()->getContent(), true);

        $tickets = $data['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $tickets);
    }
}
