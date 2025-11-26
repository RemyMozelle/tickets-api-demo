<?php

namespace App\Tests;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\GroupContextKeys;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketCommentTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldHaveCorrectSerialedKeys(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $comment = ApiHelper::getResponseDecoded($client)['data'][0];

        $this->assertSerializedKeys(GroupContextKeys::COMMENT_READ, $comment);
    }

    public function testShouldHaveCommentsForATicket(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $comments = ApiHelper::getResponseDecoded($client)['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $comments);
    }

    public function testShouldShowACommentFromATicket(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments/1');
        $this->assertResponseIsSuccessful();

        $comment = ApiHelper::getResponseDecoded($client);
        $this->assertSerializedKeys(GroupContextKeys::COMMENT_READ, $comment);
    }
}
