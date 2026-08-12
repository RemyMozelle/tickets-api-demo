<?php

namespace App\OpenApi\Attribute;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class UnauthorizedResponse extends OA\Response
{
    public function __construct(
        int $response = 401,
        string $description = 'Unauthorized response',
    ) {

        $content = new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    example: 401
                ),
                new OA\Property(
                    property: 'content',
                    type: 'string',
                    example: 'Expired JWT Token'
                ),
            ],
        );

        parent::__construct(
            response: $response,
            description: $description,
            content: $content,
        );
    }
}
