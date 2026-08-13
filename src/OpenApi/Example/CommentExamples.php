<?php

namespace App\OpenApi\Example;

final readonly class CommentExamples
{
    public const SHOW = [
        'id' => 1,
        'content' => 'comment 1 from ticket 1',
        'created_at' => '2025-09-29T12:00:00+04:00',
        'updated_at' => '2025-09-29T12:00:00+04:00',
    ];

    public const PAGINATED_LIST = [
        'data' => [
            self::SHOW,
            [
                'id' => 2,
                'content' => 'comment 2 from ticket 1',
                'created_at' => '2025-09-30T12:00:00+04:00',
                'updated_at' => '2025-09-30T12:00:00+04:00',
            ],
            [
                'id' => 3,
                'content' => 'comment 3 from ticket 1',
                'created_at' => '2025-10-01T08:00:00+04:00',
                'updated_at' => '2025-10-01T08:00:00+04:00',
            ],
        ],
        'meta' => [
            'total' => 3,
            'per_page' => 12,
            'current_page' => 1,
            'total_pages' => 1,
        ],
        'links' => [
            'first' => 'https://tickets.ddev.site/api/tickets/1/comments?page=1&limit=12',
            'last' => 'https://tickets.ddev.site/api/tickets/1/comments?page=1&limit=12',
            'prev' => null,
            'next' => null,
            'current' => 'https://tickets.ddev.site/api/tickets/3/comments?limit=2&page=2',
        ],
    ];

    public const EMPTY_FIELD = [
        'type' => 'https://symfony.com/errors/validation',
        'title' => 'Validation Failed',
        'status' => 422,
        'detail' => 'content: This value should not be blank.',
        'violations' => [
            [
                'propertyPath' => 'content',
                'title' => 'This value should not be blank.',
                'template' => 'This value should not be blank.',
                'parameters' => [
                    '{{ value }}' => '""',
                ],
            ],
        ],
    ];
}
