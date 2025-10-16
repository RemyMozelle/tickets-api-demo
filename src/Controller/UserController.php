<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
    public function getUsers(
        #[MapQueryString()] PaginationDto $paginationDto,
    ): JsonResponse {
        $users = $this->userRepository->getUsers($paginationDto);
        $total = $this->userRepository->count([]);

        return $this->apiResponse->createApiResponseWithPagination(
            data: $users,
            paginationDto: $paginationDto,
            total: $total,
            context: [
                'groups' => ['user:read'],
            ]
        );
    }

    #[Route(path: '/{user}', name: 'app_user_detail', methods: ['GET', 'POST'])]
    public function getUserDetail(
        User $user
    ): JsonResponse {
        return $this->apiResponse->createApiResponse(
            data: $user,
            context: [
                'groups' => ['user:read']
            ]
        );
    }

    #[Route('/{user}/comments', name: 'app_user_comments')]
    public function getUserComments(
        User $user,
        #[MapQueryString()] PaginationDto $paginationDto,
    ): JsonResponse {

        $comments = $this->commentRepository->findByUser(userId: $user->getId(), paginationDto: $paginationDto);
        $total = $this->commentRepository->count(['user' => $user]);

        return $this->apiResponse->createApiResponseWithPagination(
            data: $comments,
            paginationDto: $paginationDto,
            total: $total,
            context: [
                'groups' => ['comment:read'],
            ]
        );
    }

    #[Route('/{user}/tickets', name: 'app_user_tickets')]
    public function getUserTickets(
        User $user,
        #[MapQueryString()] PaginationDto $paginationDto,
        #[MapQueryString()] TicketFiltersDto $ticketFiltersDto,
    ): JsonResponse {

        $filters = array_filter([
            Ticket::FIELD_STATUS => $ticketFiltersDto->status,
            Ticket::FIELD_PRIORITY => $ticketFiltersDto->priority,
        ]);

        $tickets = $this->ticketRepository->findByUser(userId: $user->getId(), paginationDto: $paginationDto, filters: $filters);
        $total = $this->ticketRepository->count(['user' => $user, ...$filters]);

        return $this->apiResponse->createApiResponseWithPagination(
            data: $tickets,
            paginationDto: $paginationDto,
            total: $total,
            context: [
                'groups' => ['ticket:read']
            ]
        );
    }
}
