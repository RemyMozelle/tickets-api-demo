<?php

namespace App\OpenApi\Example;

final readonly class TicketExamples
{
    public const PAGINATED_LIST = [
        'data' => [
            [
                'id' => 3,
                'title' => 'Issue 3',
                'description' => 'Eveniet unde iste inventore similique nemo labore fuga. Id sequi asperiores aut unde expedita. Eos sint eveniet tempore alias est est odit. Itaque saepe est aliquam asperiores repudiandae.',
                'status' => 'closed',
                'priority' => 'high',
                'created_at' => '2024-10-03T09:00:00+04:00',
                'updated_at' => '2024-10-04T14:00:00+04:00',
                'user' => [
                    'id' => 3,
                ],
            ],
            [
                'id' => 4,
                'title' => 'Issue 4',
                'description' => 'Molestiae rerum et iure exercitationem veritatis. Veritatis qui aut in voluptatem amet. Ad explicabo iure similique. Sit dolores excepturi ratione quia fugiat.',
                'status' => 'closed',
                'priority' => 'high',
                'created_at' => '2024-10-05T12:00:00+04:00',
                'updated_at' => '2024-10-06T15:00:00+04:00',
                'user' => [
                    'id' => 4,
                ],
            ],
        ],
        'meta' => [
            'total' => 10,
            'per_page' => 2,
            'current_page' => 2,
            'total_pages' => 5,
        ],
        'links' => [
            'first' => 'https://tickets.ddev.site/api/tickets?page=1&limit=2',
            'last' => 'https://tickets.ddev.site/api/tickets?page=5&limit=2',
            'prev' => 'https://tickets.ddev.site/api/tickets?page=1&limit=2',
            'next' => 'https://tickets.ddev.site/api/tickets?page=3&limit=2',
            'current' => 'https://tickets.ddev.site/api/tickets?limit=2&page=2',
        ],
    ];
}
