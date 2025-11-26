<?php

namespace app\Tests\Functional;

use App\Repository\CommentRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\AuthHelper;
use App\Tests\Helper\GroupContextKeys;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketCommentPatchTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessUpdateTicketCommentData')]
    public function testShouldUpdateACommentForATicket(array $parameters, $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $ticketId = $parameters['ticket_id'];
        $commentId = $parameters['comment_id'];

        $uri = sprintf('/tickets/%s/comments/%s', $ticketId, $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $data);
        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $comment = ApiHelper::getResponseDecoded($client);

        $this->assertSerializedKeys(GroupContextKeys::COMMENT_READ, $comment);

        /** @var Comment $commentUpdated */
        $commentUpdated = $commentRepository->find($commentId);
        // Check Bdd
        $this->assertEquals($comment['content'], $commentUpdated->getContent());
    }

    public static function provideSuccessUpdateTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            [
                'ticket_id' => 1,
                'comment_id' => 3,
            ],
            [
                'content' => 'content updated test 1',
            ],
            200
        ];
    }

    #[DataProvider('provideFailUpdateTicketCommentData')]
    public function testShouldNotUpdateACommentForATicket(array $parameters, $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $ticketId = $parameters['ticket_id'];
        $commentId = $parameters['comment_id'];

        $uri = sprintf('/tickets/%s/comments/%s', $ticketId, $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $data);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailUpdateTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            [
                'ticket_id' => 1,
                'comment_id' => 5,
            ],
            [
                'content' => 'content updated test 1',
            ],
            404
        ];
    }
}
