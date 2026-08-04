<?php

namespace App\Tests\Functional\User;

use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;

class UserTest extends ApiTestCase
{
    public function testShouldReturnUsers(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/users', ApiTestCase::API_PREFIX);

        $client->jsonRequest('GET', $uri);

        $response = ApiHelper::getResponseDecoded(client: $client, associative: true);
        $users = $response['data'];

        [$user] = $users;

        $this->assertResponseIsSuccessful();
        $this->assertEqualsCanonicalizing(ApiResponseField::USER_READ, array_keys($user));
    }

    public function testShouldAllowUserDetailWhenUserIsNotAdmin(): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/users/%d', ApiTestCase::API_PREFIX, 1);

        $client->jsonRequest('GET', $uri);

        $user = ApiHelper::getResponseDecoded(client: $client, associative: false);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($user->id, 1);
    }

    public function testDenyUserDetailIfNotAdmin(): void
    {
        $client = static::createClient();

        $uri = sprintf('%s/users/%d', ApiTestCase::API_PREFIX, 1);

        $client->jsonRequest('GET', $uri);

        $response = ApiHelper::getResponseDecoded(client: $client);

        $this->assertResponseStatusCodeSame(401);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
    }
}
