<?php

namespace App\Tests\Functional\Comment;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CommentCreateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    /**
     * @param array<string, string> $body
     * @param array<string, int> $data
     */
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
        $this->assertSame($body['content'], $commentFromResponse['content']);
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

    /**
     * @param array<string, mixed> $body
     */
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

    public function testShouldAllowUserToCreateCommentWhenOwnerOfTicket(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_2_with_2_tickets@gmail.com', 'user');
        $ticketRepository  = static::getContainer()->get(TicketRepository::class);
        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $security = static::getContainer()->get(Security::class);

        /** @var User $authenticatedUser */
        $authenticatedUser = $security->getUser();

        $ticket = $ticketRepository->findOneBy([
            'user' => $authenticatedUser,
            'title' => 'Issue 1',
        ]);

        $this->assertNotNull($ticket);

        $this->assertSame(
            $authenticatedUser->getId(),
            $ticket->getUser()->getId()
        );

        $uri = sprintf('/tickets/%s/comments', $ticket->getId());

        $client->jsonRequest(method: 'POST', uri: $uri, parameters: [
            'content' => 'Comment test'
        ]);

        $this->assertResponseStatusCodeSame(201);

        // Check BDD
        $commentAfterRequest = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
            'ticket' => $ticket,
            'content' => 'Comment test',
        ]);

        $this->assertNotNull($commentAfterRequest);
    }


    public function testShouldAllowUserToCreateCommentWhenTicketIsNotOwnedByUser(): void
    {
        $client = AuthHelper::createAuthenticatedClient('user_3_with_1_ticket@gmail.com', 'user');

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);
        $security = static::getContainer()->get(Security::class);

        /** @var User $authenticatedUser */
        $authenticatedUser = $security->getUser();

        $ticketOwner = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($ticketOwner);

        $ticket = $ticketRepository->findOneBy([
            'user' => $ticketOwner,
            'title' => 'Issue 1',
        ]);

        $this->assertNotNull($ticket);

        $this->assertNotSame(
            $authenticatedUser->getId(),
            $ticket->getUser()->getId()
        );

        $client->jsonRequest(
            method: 'POST',
            uri: sprintf('/tickets/%s/comments', $ticket->getId()),
            parameters: [
                'content' => 'Comment test'
            ]
        );

        $this->assertResponseStatusCodeSame(201);

        $commentAfterRequest = $commentRepository->findOneBy([
            'user' => $authenticatedUser,
            'ticket' => $ticket,
            'content' => 'Comment test',
        ]);

        $this->assertNotNull($commentAfterRequest);
    }

    public function testShouldDenyUserToCreateCommentWhenIsNotAuthenticated(): void
    {
        $client = static::createClient();

        $ticketRepository = static::getContainer()->get(TicketRepository::class);
        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $userRepository = static::getContainer()->get(UserRepository::class);

        $ownerUser = $userRepository->findOneBy([
            'email' => 'user_2_with_2_tickets@gmail.com',
        ]);

        $this->assertNotNull($ownerUser);

        $ticket = $ticketRepository->findOneBy([
            'user' => $ownerUser,
            'title' => 'Issue 1',
        ]);

        $this->assertNotNull($ticket);

        $body = [
            'content' => 'Comment test',
        ];

        $client->jsonRequest(
            method: 'POST',
            uri: sprintf('/tickets/%s/comments', $ticket->getId()),
            parameters: $body
        );

        $this->assertResponseStatusCodeSame(401);

        $commentAfterRequest = $commentRepository->findOneBy([
            'ticket' => $ticket,
            'content' => $body['content'],
        ]);

        $this->assertNull($commentAfterRequest);
    }
}
