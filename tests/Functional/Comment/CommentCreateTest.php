<?php

namespace App\Tests\Functional\Comment;

use App\Repository\TicketRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentCreateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideValideCommentData')]
    public function testShouldCreateACommentForATicket(array $body, array $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $ticketRepositoty  = static::getContainer()->get(TicketRepository::class);
        $ticketId = $data['ticket_id'];

        $uri = sprintf('/tickets/%s/comments', $ticketId);
        $client->jsonRequest(method: 'POST', uri: $uri, parameters: $body);

        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $commentFromResponse = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $commentFromResponse);

        //Check BDD
        $ticket = $ticketRepositoty->findOneBy(['id' => $ticketId]);
        $ticketComments = $ticket->getComments();
        $this->assertCount($data['expected_nb_comments'], $ticketComments);

        //Check JSON
        $this->assertSame(expected: $body['content'], actual: $commentFromResponse['content']);
    }

    public static function provideValideCommentData(): \Generator
    {
        $cases = [
            'ticket 1' => [
                'body' => [
                    'content' => 'content test 2',
                ],
                'data' => [
                    'ticket_id' => 1,
                    'expected_nb_comments' => 4
                ],
                'status_code' => 201,
            ],
            'ticket 2' => [
                'body' => [
                    'content' => 'content test 2',
                ],
                'data' => [
                    'ticket_id' => 3,
                    'expected_nb_comments' => 3
                ],
                'status_code' => 201,
            ],
        ];

        $casesNumber = 0;
        foreach ($cases as $label => $case) {
            $nbComments = $case['data']['expected_nb_comments'];
            ++$casesNumber;

            yield sprintf(
                'Case n°%d Should have %d comments for %s',
                $casesNumber,
                $nbComments,
                $label
            ) => [
                $case['body'],
                $case['data'],
                $case['status_code'],
            ];
        }
    }

    #[DataProvider('provideInvalidCommentData')]
    public function testShouldNotCreateACommentForATicket(array $body, int $ticketId, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $uri = sprintf('/tickets/%s/comments', $ticketId);

        $client->jsonRequest(method: 'POST', uri: $uri, parameters: $body);

        $response = ApiHelper::getResponseDecoded($client);

        $violations = $response['violations'];

        $actualFields = array_column(
            $violations,
            'propertyPath'
        );

        $this->assertEqualsCanonicalizing($actualFields, ['content']);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideInvalidCommentData(): \Generator
    {
        yield 'Should not add a "comment" without content filled' => [
            [
                'content' => '',
            ],
            1,
            422,
        ];

        yield 'Should not add a "comment" with content to null' => [
            [
                'content' => null,
            ],
            1,
            422,
        ];
    }
}
