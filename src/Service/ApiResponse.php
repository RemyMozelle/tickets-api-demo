<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResponse
{
    public function __construct(private readonly SerializerInterface $serializer) {}

    public function createApiResponse(mixed $data, int $page, int $total, int $limit, array $context = [])
    {
        $data = $this->serializer->serialize(data: $data, format: 'json', context: [
            'groups' => ['user:read', 'comment:read', 'ticket:read']
        ]);

        return new JsonResponse([
            'data' => json_decode($data, true),
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page === 0 ? $page = 1 : $page,
                'total_pages' => ceil($total / $limit),
            ],
            'links' => [
                'first' => "",
                'last' => "",
                'next' => "",
                'prev' => "",
            ],
        ]);
    }
}
