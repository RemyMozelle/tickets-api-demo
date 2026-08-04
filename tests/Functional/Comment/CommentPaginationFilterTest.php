<?php

namespace App\Tests\Functional\Comment;

use App\Tests\Functional\ApiTestCase;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class CommentPaginationFilterTest extends ApiTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, int> $queryParameters
     * @param array<string, int> $expectedMeta
     * @param array<string, string|null> $expectedLinks
     * @param list<string> $expectedCommentContent
     */
    #[DataProvider('provideMultipleFilterData')]
    public function testShouldFilterCommentsByMultipleQueryParameters(array $queryParameters, array $expectedMeta, array $expectedLinks, array $expectedCommentContent): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/users/%d/comments', ApiTestCase::API_PREFIX, 2);

        $client->request(method: 'GET', uri: $uri, parameters: $queryParameters);
        $this->assertResponseIsSuccessful();

        $response = ApiHelper::getResponseDecoded($client);

        $commentsFromResponse = $response['data'];
        $metaFromResponse = $response['meta'];

        $commentsContent = array_column($commentsFromResponse, 'content');

        $this->assertEqualsCanonicalizing($expectedCommentContent, $commentsContent);
        $this->assertEqualsCanonicalizing($expectedMeta, $metaFromResponse);
    }

    /**
     * @param array<string, int> $queryParameters
     * @param array<string, int> $expectedMeta
     * @param array<string, string|null> $expectedLinks
     * @param list<string> $expectedCommentContent
     */
    #[DataProvider('provideMultipleFilterData')]
    public function testShouldGeneratePaginationLinks(array $queryParameters, array $expectedMeta, array $expectedLinks, array $expectedCommentContent): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('%s/users/%d/comments', ApiTestCase::API_PREFIX, 2);

        $client->request(method: 'GET', uri: $uri, parameters: $queryParameters);
        $this->assertResponseIsSuccessful();

        $response = ApiHelper::getResponseDecoded($client);

        $links = $response['links'];

        foreach ($expectedLinks as $name => $expectedLink) {
            if ($expectedLink === null) {
                $this->assertNull($links[$name]);
                continue;
            }

            $expectedLinkQuery = parse_url($expectedLink, PHP_URL_QUERY);
            $actualQuery = parse_url($links[$name], PHP_URL_QUERY);

            $this->assertIsString($expectedLinkQuery);
            $this->assertIsString($actualQuery);

            parse_str(
                $expectedLinkQuery,
                $expectedParameters
            );

            parse_str(
                $actualQuery,
                $actualParameters
            );

            $this->assertEqualsCanonicalizing(
                $expectedParameters,
                $actualParameters,
                'Parameters from url are not equals.'
            );
        }
    }

    public static function provideMultipleFilterData(): \Generator
    {
        $cases = [
            [
                'query' => [
                    'limit' => 1,
                ],
                'meta' => [
                    'total' => 4,
                    'per_page' => 1,
                    'current_page' => 1,
                    'total_pages' => 4,
                ],
                'links' => [
                    'first' => '/users/2/comments?limit=1&page=1',
                    'last' => '/users/2/comments?limit=1&page=4',
                    'prev' => null,
                    'next' => '/users/2/comments?limit=1&page=2',
                    'current' => '/users/2/comments?limit=1',
                ],
                'comment_content' => [
                    'comment 2 from ticket 1',
                ],
            ],
            [
                'query' => [
                    'limit' => 2,
                ],
                'meta' => [
                    'total' => 4,
                    'per_page' => 2,
                    'current_page' => 1,
                    'total_pages' => 2,
                ],
                'links' => [
                    'first' => '/users/2/comments?limit=2&page=1',
                    'last' => '/users/2/comments?limit=2&page=2',
                    'prev' => null,
                    'next' => '/users/2/comments?limit=2&page=2',
                    'current' => '/users/2/comments?limit=2',
                ],
                'comment_content' => [
                    'comment 2 from ticket 1',
                    'comment 1 from ticket 2',
                ],
            ],
            [
                'query' => [
                    'limit' => 2,
                    'page' => 2,
                ],
                'meta' => [
                    'total' => 4,
                    'per_page' => 2,
                    'current_page' => 2,
                    'total_pages' => 2,
                ],
                'links' => [
                    'first' => '/users/2/comments?limit=2&page=1',
                    'last' => '/users/2/comments?limit=2&page=2',
                    'prev' => '/users/2/comments?limit=2&page=1',
                    'next' => null,
                    'current' => '/users/2/comments?limit=2&page=2',
                ],
                'comment_content' => [
                    'comment 2 from ticket 2',
                    'comment 1 from ticket 1',
                ],
            ],
            [
                'query' => [
                    'limit' => 4,
                ],
                'meta' => [
                    'total' => 4,
                    'per_page' => 4,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
                'links' => [
                    'first' => '/users/2/comments?limit=4&page=1',
                    'last' => '/users/2/comments?limit=4&page=1',
                    'prev' => null,
                    'next' => null,
                    'current' => '/users/2/comments?limit=4',
                ],
                'comment_content' => [
                    'comment 1 from ticket 1',
                    'comment 2 from ticket 1',
                    'comment 1 from ticket 2',
                    'comment 2 from ticket 2',
                ],
            ],
        ];

        foreach ($cases as $key => $case) {
            yield sprintf(
                'Case n°%d Should return %d comments by limit of %s with filters [%s] for a total [%s comments]',
                $key + 1,
                count($case['comment_content']),
                $case['query']['limit'],
                implode(', ', array_keys($case['query'])),
                $case['meta']['total'],
            ) => [
                $case['query'],
                $case['meta'],
                $case['links'],
                $case['comment_content'],
            ];
        }
    }
}
