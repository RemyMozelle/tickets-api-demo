<?php

namespace App\OpenApi\Attribute;

use App\OpenApi\Schema\ValidationErrorResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ViolationResponse extends OA\Response
{
    /**
     * @param class-string $type
     */
    public function __construct(
        string $type = ValidationErrorResponse::class,
        int $response = 422,
        mixed $example = null,
        string $description = 'Violation response',
    ) {
        $content = new OA\JsonContent(
            ref: new Model(type: $type),
            example: [
                'type' => 'https://symfony.com/errors/validation',
                'title' => 'Validation Failed',
                'status' => $response,
                'detail' => 'status: The "status" with value "a" is not valid',
                'violations' => [
                    [
                        'propertyPath' => 'status',
                        'title' => 'The "status" with value "a" is not valid',
                        'template' => 'The {{ property_name }} with value {{ value }} is not valid',
                        'parameters' => [
                            '{{ property_name }}' => 'status',
                            '{{ value }}' => 'a',
                        ],
                    ],
                ],
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
