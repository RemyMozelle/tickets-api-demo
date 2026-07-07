<?php

namespace App\Tests\Functional\Comment;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldHaveCorrectSerialedKeys(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $comment = ApiHelper::getResponseDecoded($client)['data'][0];

        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $comment);
    }

    public function testShouldHaveCommentsForATicket(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/tickets/1/comments');

        $comments = ApiHelper::getResponseDecoded($client)['data'];

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $comments);
    }

    public function testShouldShowAComment(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/comments/1');
        $this->assertResponseIsSuccessful();

        $comment = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $comment);
    }
}
