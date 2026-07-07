<?php

namespace App\Tests\Functional\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Helper\AuthHelper;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentUpdateTest extends WebTestCase
{
    use ApiTestAssertionsTrait;

    #[DataProvider('provideSuccessUpdateTicketCommentData')]
    public function testShouldUpdateACommentForATicket(array $parameters, $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();
        $commentRepository = static::getContainer()->get(CommentRepository::class);

        $commentId = $parameters['comment_id'];

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $data);
        $this->assertResponseStatusCodeSame($expectedStatusCode);

        $comment = ApiHelper::getResponseDecoded($client);

        $this->assertResponseApiField(ApiResponseField::COMMENT_READ, $comment);

        // Check Json
        $this->assertEquals($data['content'], $comment['content']);

        /** @var Comment $commentUpdated */
        $commentUpdated = $commentRepository->find($commentId);
        // Check Bdd
        $this->assertEquals($data['content'], $commentUpdated->getContent());
    }

    public static function provideSuccessUpdateTicketCommentData(): \Generator
    {
        yield 'Should update ticket with "content" filled' => [
            [
                'comment_id' => 3,
            ],
            [
                'content' => 'content updated test 1',
            ],
            200
        ];
    }

    #[DataProvider('provideFailUpdateTicketCommentData')]
    public function testShouldNotUpdateAComment(array $parameters, $data, int $expectedStatusCode): void
    {
        $client = AuthHelper::createAuthenticatedClient();

        $commentId = $parameters['comment_id'];

        $uri = sprintf('/comments/%s', $commentId);

        $client->jsonRequest(method: 'PATCH', uri: $uri, parameters: $data);
        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }

    public static function provideFailUpdateTicketCommentData(): \Generator
    {
        yield 'Should not update a "comment" who do not exist' => [
            [
                'comment_id' => 1000,
            ],
            [
                'content' => 'content updated test 1',
            ],
            404
        ];

        yield 'Should not update ticket with "content" empty' => [
            [
                'comment_id' => 3,
            ],
            [
                'content' => '',
            ],
            422
        ];

        yield 'Should not update ticket with "content" null' => [
            [
                'comment_id' => 3,
            ],
            [
                'content' => null,
            ],
            422
        ];
    }
}
