<?php

namespace App\OpenApi\Example;

final readonly class UserExamples
{
    public const SHOW = [
        'id' => 1,
        'email' => 'admin_1@gmail.com',
    ];

    public const PAGINATED_LIST = [
        'data' => [
            self::SHOW,
            [
                'id' => 2,
                'email' => 'user_2_with_2_tickets@gmail.com',
            ],
            [
                'id' => 3,
                'email' => 'user_3_with_1_ticket@gmail.com',
            ],
        ],
        'meta' => [
            'total' => 10,
            'per_page' => 2,
            'current_page' => 2,
            'total_pages' => 5,
        ],
        'links' => [
            'first' => 'https://tickets.ddev.site/api/users?page=1&limit=2',
            'last' => 'https://tickets.ddev.site/api/users?page=5&limit=2',
            'prev' => 'https://tickets.ddev.site/api/users?page=1&limit=2',
            'next' => 'https://tickets.ddev.site/api/users?page=3&limit=2',
            'current' => 'https://tickets.ddev.site/api/users?limit=2&page=2',
        ],
    ];
}
