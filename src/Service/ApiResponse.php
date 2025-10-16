<?php

namespace App\Service;

use App\Dto\PaginationDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ApiResponse
{
    public function __construct(private readonly SerializerInterface $serializer) {}

    public function createApiResponse(
        object $data,
        array $context = [],
        int $status = 200
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse([
            'data' => json_decode($jsonData, true) ?: [],
        ], $status);
    }

    public function createApiResponseWithPagination(
        array $data,
        PaginationDto $paginationDto,
        int $total = 0,
        array $context = [],
        int $status = 200
    ): JsonResponse {
        $jsonData = $this->serializer->serialize($data, 'json', $context);

        $response = [
            'data' => json_decode($jsonData, true) ?: [],
            'meta' => [
                'total' => $total,
                'per_page' => $paginationDto->limit,
                'current_page' => $paginationDto->page,
                'total_pages' => (int) ceil($total / $paginationDto->limit),
            ],
            'links' => [
                'first' => '',
                'last'  => '',
                'next'  => '',
                'prev'  => '',
            ],
        ];

        return new JsonResponse($response, $status);
    }
}
