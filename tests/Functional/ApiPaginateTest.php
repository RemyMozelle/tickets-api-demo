<?php

namespace App\Tests\Functional;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiPaginateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('providePaginatedEndpoints')]
    public function testShoudReturnsPaginatedResponseStructure(string $endPoint): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $client->request(
            method: 'GET',
            uri: $endPoint,
        );

        $response = ApiHelper::getResponseDecoded($client);

        $this->assertResponseIsSuccessful();
        $this->assertPaginationStructure($response);
    }

    #[DataProvider('providePaginatedEndpoints')]
    public function testShouldDenyUserNotAuthenticated(string $endPoint): void
    {
        $client = static::createClient();
        $client->request(
            method: 'GET',
            uri: $endPoint,
        );

        $response = ApiHelper::getResponseDecoded($client);

        $this->assertResponseStatusCodeSame(401);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * TODO: Ajouter le endpoint "/comments"
     */
    public static function providePaginatedEndpoints(): \Generator
    {
        yield 'users endpoint' => ['/users'];
        yield 'tickets endpoint' => ['/tickets'];
        yield 'user tickets endpoint' => ['/users/2/tickets'];
        yield 'user comments endpoint' => ['/users/2/comments'];
        yield 'ticket comments endpoint' => ['/tickets/1/comments'];
    }
}
