<?php

namespace App\Tests\Functional\Comment;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentListByUserTest extends WebTestCase
{
    #[DataProvider('commentsShouldHaveALimit')]
    public function testUserCommentsByLimit($dataParameters, $expectedNbComments): void
    {
        $limit = $dataParameters['query']['limit'];

        $client = static::createClient();
        $client->jsonRequest('GET', '/users/2/comments?limit=' . $limit);

        $data = json_decode($client->getResponse()->getContent(), true);

        $comments = $data['data'];
        $meta = $data['meta'];

        $expectedMeta = $dataParameters['meta'];

        $this->assertResponseIsSuccessful();

        $this->assertCount($expectedNbComments, $comments);
        $this->assertEquals($expectedMeta, $meta);
    }

    public static function commentsShouldHaveALimit(): \Generator
    {
        yield 'User 2 Should have 2 comments with limit 2' => [
            [
                'query' => [
                    'limit' => 2,
                ],
                'meta' => [
                    'per_page' => 2,
                    'total' => 4,
                    'current_page' => 1,
                    'total_pages' => 2,
                ],
            ],
            2
        ];

        yield 'User 2 Should have 1 comment with limit 1' => [
            [
                'query' => [
                    'limit' => 1,
                ],
                'meta' => [
                    'per_page' => 1,
                    'total' => 4,
                    'current_page' => 1,
                    'total_pages' => 4,
                ],
            ],
            1
        ];
    }
}
