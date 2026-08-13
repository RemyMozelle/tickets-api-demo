<?php

namespace App\OpenApi\Attribute;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ModelResponse extends OA\Response
{
    /**
     * @param class-string $type
     * @param list<string>|null $groups
     */
    public function __construct(
        string $type,
        ?array $groups = null,
        mixed $example = null,
        int $response = 200,
        string $description = 'Successful response',
    ) {
        $content = new OA\JsonContent(
            ref: new Model(
                type: $type,
                groups: $groups,
            ),
        );

        if ($example !== null) {
            $content->example = $example;
        }

        parent::__construct(
            response: $response,
            description: $description,
            content: $content,
        );
    }
}
