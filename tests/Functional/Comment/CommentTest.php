<?php

namespace App\Tests\Functional\Comment;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Trait\ApiTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    public function testShouldShowComment(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/comments/1');
        $this->assertResponseIsSuccessful();

        $commentFromResponse = ApiHelper::getResponseDecoded($client);
        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);
    }
}
