<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly ApiResponse $apiResponse,
        private readonly UserRepository $userRepository,
        private readonly CommentRepository $commentRepository,
        private readonly TicketRepository $ticketRepository,
        #[Autowire(param: 'app.pagination.default.max_limit')]
        private readonly int $maxLimit,
        #[Autowire(param: 'app.pagination.default.page')]
        private readonly int $page
    ) {}

    #[Route(path: '', name: 'app_user', methods: ['GET'])]
    public function getUsers(Request $request): JsonResponse
    {
        $page = (int) $request->get('page') ?: $this->page;
        $limit = (int) $request->get('limit') ?: $this->maxLimit;

        $users = $this->userRepository->getUsers(page: $page, limit: $limit);
        $total = $this->userRepository->count([]);

        return $this->apiResponse->createApiResponse(data: $users, page: $page, total: $total, limit: $limit);
    }

    #[Route(path: '/{userId}', name: 'app_user_detail', methods: ['GET', 'POST'])]
    public function getUserDetail(
        User $userId
    ): JsonResponse
    {
        return $this->apiResponse->createApiResponse(data: $userId);
    }

    #[Route('/{userId}/comments', name: 'app_user_comments')]
    public function getUserComments(Request $request, int $userId): JsonResponse
    {
        $page = (int) $request->get('page') ?: $this->page;
        $limit = (int) $request->get('limit') ?: $this->maxLimit;

        $comments = $this->commentRepository->findByUser(userId: $userId, page: $page, limit: $limit);
        $total = $this->commentRepository->count(['user' => $userId]);

        return $this->apiResponse->createApiResponse(data: $comments, page: $page, total: $total, limit: $limit);
    }

    #[Route('/{userId}/tickets', name: 'app_user_tickets')]
    public function getUserTickets(Request $request, int $userId): JsonResponse
    {
        $page = (int) $request->get('page') ?: $this->page;
        $limit = (int) $request->get('limit') ?: $this->maxLimit;
        $status = $request->get(Ticket::FIELD_STATUS);
        $priority = $request->get(Ticket::FIELD_PRIORITY);

        $filters = array_filter([
            Ticket::FIELD_STATUS => $status,
            Ticket::FIELD_PRIORITY => $priority,
        ]);

        $tickets = $this->ticketRepository->findByUser(userId: $userId, page: $page, limit: $limit, filters: $filters);
        $total = $this->ticketRepository->count(['user' => $userId, ...$filters]);

        return $this->apiResponse->createApiResponse(data: $tickets, page: $page, total: $total, limit: $limit);
    }
}
