<?php

namespace App\Tests\Functional;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class ApiPaginateTest extends ApiTestCase
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
     * @param array<string, int> $queryParameters
     */
    #[DataProvider('provideInvalidPaginatedEndpoints')]
    public function testShouldReturnUnprocessableEntityForInvalidPaginationParameters(string $endPoint, array $queryParameters): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $client->request(
            method: 'GET',
            uri: $endPoint,
            parameters: $queryParameters,
        );

        $response = ApiHelper::getResponseDecoded($client);

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('violations', $response);
        $this->assertNotEmpty($response['violations']);
    }

    /**
     * TODO: Ajouter le endpoint "/comments"
     */
    public static function providePaginatedEndpoints(): \Generator
    {
        yield 'users endpoint' => [ApiTestCase::API_PREFIX . '/users'];
        yield 'tickets endpoint' => [ApiTestCase::API_PREFIX . '/tickets'];
        yield 'user tickets endpoint' => [ApiTestCase::API_PREFIX . '/users/2/tickets'];
        yield 'user comments endpoint' => [ApiTestCase::API_PREFIX . '/users/2/comments'];
        yield 'ticket comments endpoint' => [ApiTestCase::API_PREFIX . '/tickets/1/comments'];
    }

    /**
     * @return \Generator<string, array{string, array<string, int>}>
     */
    public static function provideInvalidPaginatedEndpoints(): \Generator
    {
        foreach (self::providePaginatedEndpoints() as $name => [$endPoint]) {
            yield $name => [
                $endPoint, [
                    'page' => 0,
                    'limit' => 0,
                ]];
        }
    }
}
