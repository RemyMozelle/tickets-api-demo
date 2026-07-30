<?php

namespace App\Tests\Functional\Ticket;

use App\Enum\Priority;
use App\Enum\Status;
use App\Tests\Helper\ApiHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketPaginationFilterTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, mixed> $queryParameters
     * @param array<string, int> $expectedMeta
     * @param array<string, string|null> $expectedLinks
     * @param list<string> $expectedTicketTitles
     */
    #[DataProvider('provideMultipleFilterData')]
    public function testShouldFilterTicketsByMultipleQueryParameters(array $queryParameters, array $expectedMeta, array $expectedLinks, array $expectedTicketTitles): void
    {
        $client = static::createClient();

        $client->request(method: 'GET', uri: '/tickets', parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $tickets = $response['data'];
        $meta = $response['meta'];

        foreach ($tickets as $ticket) {
            $this->assertContains($ticket['title'], $expectedTicketTitles);
        }

        $this->assertResponseIsSuccessful();
        $this->assertEqualsCanonicalizing($expectedMeta, $meta);
    }

    /**
     * @param array<string, mixed> $queryParameters
     * @param array<string, int> $expectedMeta
     * @param array<string, string|null> $expectedLinks
     * @param list<string> $expectedTicketTitles
     */
    #[DataProvider('provideMultipleFilterData')]
    public function testShouldGeneratePaginationLinks(array $queryParameters, array $expectedMeta, array $expectedLinks, array $expectedTicketTitles): void
    {
        $client = static::createClient();

        $client->request(method: 'GET', uri: '/tickets', parameters: $queryParameters);

        $response = ApiHelper::getResponseDecoded($client);

        $links = $response['links'];

        foreach ($expectedLinks as $name => $expectedLink) {
            if ($expectedLink === null) {
                $this->assertNull($links[$name]);

                continue;
            }

            parse_str(
                parse_url($expectedLink, PHP_URL_QUERY),
                $expectedParameters
            );

            parse_str(
                parse_url($links[$name], PHP_URL_QUERY),
                $actualParameters
            );

            $this->assertEqualsCanonicalizing(
                $expectedParameters,
                $actualParameters
            );
        }

        $this->assertResponseIsSuccessful();
    }

    public static function provideMultipleFilterData(): \Generator
    {
        $cases = [
            [
                'query' => [
                    'status' => Status::Open->value,
                    'priority' => Priority::High->value,
                    'start_date' => '2023-09-30',
                    'end_date' => '2025-01-01',
                    'start_time' => '12:00:00',
                    'end_time' => '07:00:00',
                ],
                'meta' => [
                    "total" => 1,
                    "per_page" => 12,
                    "current_page" => 1,
                    "total_pages" => 1,
                ],
                'links' => [
                    "first" => "/tickets?status=open&priority=high&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=12",
                    "last" => "/tickets?status=open&priority=high&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=12",
                    "prev" => null,
                    "next" => null,
                    "current" => "/tickets?status=open&priority=high&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00",
                ],
                'ticket_titles' => [
                    'Issue 1'
                ],
            ],
            [
                'query' => [
                    'limit' => 1,
                    'page' => 1,
                    'status' => Status::Open->value,
                    'priority' => [
                        Priority::High->value,
                        Priority::Low->value,
                    ],
                    'start_date' => '2023-09-30',
                    'end_date' => '2025-01-01',
                    'start_time' => '12:00:00',
                    'end_time' => '07:00:00',
                ],
                'meta' => [
                    "total" => 2,
                    "per_page" => 1,
                    "current_page" => 1,
                    "total_pages" => 2
                ],
                'links' => [
                    "first" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=1",
                    "last" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=2&limit=1",
                    "prev" => null,
                    "next" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=2&limit=1",
                    "current" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=1",
                ],
                'ticket_titles' => [
                    'Issue 1',
                    'Issue 5'
                ],
            ],
            [
                'query' => [
                    'limit' => 1,
                    'page' => 2,
                    'status' => Status::Open->value,
                    'priority' => [
                        Priority::High->value,
                        Priority::Low->value,
                    ],
                    'start_date' => '2023-09-30',
                    'end_date' => '2025-01-01',
                    'start_time' => '12:00:00',
                    'end_time' => '07:00:00',
                ],
                'meta' => [
                    "total" => 2,
                    "per_page" => 1,
                    "current_page" => 2,
                    "total_pages" => 2
                ],
                'links' => [
                    "first" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=1",
                    "last" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=2&limit=1",
                    "prev" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=1",
                    "next" => null,
                    "current" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=2&limit=1",
                ],
                'ticket_titles' => [
                    'Issue 1',
                    'Issue 5'
                ],
            ],
            [
                'query' => [
                    'limit' => 2,
                    'page' => 1,
                    'status' => Status::Open->value,
                    'priority' => [
                        Priority::High->value,
                        Priority::Low->value,
                    ],
                    'start_date' => '2023-09-30',
                    'end_date' => '2025-01-01',
                    'start_time' => '12:00:00',
                    'end_time' => '07:00:00',
                ],
                'meta' => [
                    "total" => 2,
                    "per_page" => 2,
                    "current_page" => 1,
                    "total_pages" => 1
                ],
                'links' => [
                    "first" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=2",
                    "last" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=2",
                    "prev" => null,
                    "next" => null,
                    "current" => "/tickets?status=open&priority[]=high&priority[]=low&start_date=2023-09-30&end_date=2025-01-01&start_time=12:00:00&end_time=07:00:00&page=1&limit=2",
                ],
                'ticket_titles' => [
                    'Issue 1',
                    'Issue 5'
                ],
            ],
        ];

        foreach ($cases as $key => $case) {
            yield sprintf(
                'case n°%d should return %d tickets with filters [%s]',
                $key + 1,
                $case['meta']['total'],
                implode(', ', array_keys($case['query'])),
            ) => [
                $case['query'],
                $case['meta'],
                $case['links'],
                $case['ticket_titles']
            ];
        }
    }
}
