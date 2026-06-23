<?php

namespace App\Tests\Functional\User;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testShouldReturnUsers()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/users');

        $response = ApiHelper::getResponseDecoded(client: $client, associative: true);
        $users = $response['data'];

        [$user] = $users;

        $this->assertResponseIsSuccessful();
        $this->assertEqualsCanonicalizing(ApiResponseField::USER_READ, array_keys($user));
    }

    public function testUserDetail()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/users/1');

        $user = json_decode($client->getResponse()->getContent());

        $this->assertResponseIsSuccessful();

        $this->assertEquals($user->id, 1);
    }
}

