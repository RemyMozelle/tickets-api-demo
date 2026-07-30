<?php

namespace App\Tests\Functional\Comment;

use App\Tests\Helper\ApiHelper;
use App\Tests\Helper\ApiResponseField;
use App\Tests\Trait\ApiTestAssertionsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentListByUserTest extends WebTestCase
{
    use ApiTestAssertionsTrait;
    
    /**
     * @param list<string> $expectedCommentContent
     */
    #[DataProvider('provideFilterUserData')]
    public function testShouldReturnCommentsForGivenUser(int $userId, array $expectedCommentContent): void
    {
        $client = static::createClient();
        $client->request(method: 'GET', uri: sprintf('/users/%d/comments', $userId));

        $response = ApiHelper::getResponseDecoded($client);
        $this->assertResponseIsSuccessful();

        $commentsFromResponse = $response['data'];
        $nbComments = count($expectedCommentContent);

        $this->assertResponseApiField(expectedKeys: ApiResponseField::COMMENT_READ, actual: current($commentsFromResponse));
        $this->assertEqualsCanonicalizing($expectedCommentContent, array_column($commentsFromResponse, 'content'));
        $this->assertCount($nbComments, $commentsFromResponse);
    }

    public static function provideFilterUserData(): \Generator
    {
        $cases = [
            [
                'user_id' => 1,
                'comment_content' => [
                    'comment 3 from ticket 1',
                    'comment 3 from ticket 2',
                    'comment 2 from ticket 3',
                ]
            ],
            [
                'user_id' => 2,
                'comment_content' => [
                    'comment 1 from ticket 1',
                    'comment 2 from ticket 1',
                    'comment 1 from ticket 2',
                    'comment 2 from ticket 2',
                ]
            ],
            [
                'user_id' => 3,
                'comment_content' => [
                    'comment 1 from ticket 3',
                ]
            ]
        ];

        foreach ($cases as $key => $case) {
            yield sprintf(
                'case n°%d should return %d comments with user [%s]',
                $key + 1,
                count($case['comment_content']),
                $case['user_id'],
            ) => [
                $case['user_id'],
                $case['comment_content'],
            ];
        }
    }
}
