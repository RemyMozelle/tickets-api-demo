<?php

namespace App\OpenApi\Attribute;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class NoContentResponse extends OA\Response
{
    public function __construct(
        int $response = 204,
        string $description = 'No content',
    ) {
        parent::__construct(
            response: $response,
            description: $description,
        );
    }
}
