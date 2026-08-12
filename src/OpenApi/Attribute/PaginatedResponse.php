<?php

namespace App\OpenApi\Attribute;

use App\OpenApi\Schema\PaginationLinks;
use App\OpenApi\Schema\PaginationMeta;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class PaginatedResponse extends OA\Response
{
    /**
     * @param class-string $type
     * @param list<string> $groups
     */
    public function __construct(
        string $type,
        ?array $groups,
        mixed $example = null,
        int $response = 200,
        string $description = 'Paginated response',
    ) {
        $content = new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(
                            type: $type,
                            groups: $groups,
                        ),
                    ),
                ),
                new OA\Property(
                    property: 'meta',
                    ref: new Model(
                        type: PaginationMeta::class,
                    ),
                ),
                new OA\Property(
                    property: 'links',
                    ref: new Model(
                        type: PaginationLinks::class,
                    ),
                ),
            ],
        );

        if ($example !== null) {
            $content->example = $example;
        }

        parent::__construct(
            response: $response,
            description: $description,
            content: $content
        );
    }
}
