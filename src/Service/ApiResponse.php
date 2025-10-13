<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResponse
{
    public function __construct(private readonly SerializerInterface $serializer) {}

    public function createApiResponse(mixed $data, int $page = 1, int $total = 0, int $limit = 12, array $context = [])
    {
        $response = [];

        $jsonData = $this->serializer->serialize(data: $data, format: 'json', context: [
            'groups' => ['user:read', 'comment:read', 'ticket:read']
        ]);

        $response['data'] = json_decode($jsonData, true) ?: [];

        if (is_array($data)) {
            $response['meta'] = [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page === 0 ? $page = 1 : $page,
                'total_pages' => ceil($total / $limit),
            ];

            $response['links'] = [
                'links' => [
                    'first' => "",
                    'last' => "",
                    'next' => "",
                    'prev' => "",
                ],
            ];
        }

        return new JsonResponse($response);
    }
}
