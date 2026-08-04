<?php

namespace App\Tests\Functional\User;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    public function testShouldReturnUsers(): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $client->jsonRequest('GET', '/users');

        $response = ApiHelper::getResponseDecoded(client: $client, associative: true);
        $users = $response['data'];

        [$user] = $users;

        $this->assertResponseIsSuccessful();
        $this->assertEqualsCanonicalizing(ApiResponseField::USER_READ, array_keys($user));
    }

    public function testShouldAllowUserDetailWhenUserIsNotAdmin(): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $client->jsonRequest('GET', '/users/1');

        $user = ApiHelper::getResponseDecoded(client: $client, associative: false);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($user->id, 1);
    }

    public function testDenyUserDetailIfNotAdmin(): void
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/users/1');

        $response = ApiHelper::getResponseDecoded(client: $client);

        $this->assertResponseStatusCodeSame(401);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
    }
}
