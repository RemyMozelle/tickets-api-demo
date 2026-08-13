<?php

namespace App\OpenApi\Attribute;

use App\OpenApi\Schema\ErrorResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class NotFoundResponse extends OA\Response
{
    /**
     * @param class-string $type
     */
    public function __construct(
        string $type = ErrorResponse::class,
        int $response = 404,
        string $description = 'Not found response',
    ) {

        $content = new OA\JsonContent(
            ref: new Model(type: $type),
            example: [
                'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => 'An error occurred',
                'status' => 404,
                'detail' => 'Not Found',
            ],
        );

        parent::__construct(
            response: $response,
            description: $description,
            content: $content,
        );
    }
}
