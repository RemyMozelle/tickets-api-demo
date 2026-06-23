<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Constant\TicketGroups;
use App\Constant\UserGroups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TicketRepository $ticketRepository,
    ) {}

    #[Route(path: '', name: 'app_user_list', methods: ['GET'])]
    public function list(
        #[MapQueryString()] PaginationDto $paginationDto,
        Request $request,
    ): JsonResponse {
        $users = $this->userRepository->getUsers($paginationDto);

        return $this->json(
            data: $users,
            status: 200,
            context: [
                'groups' => UserGroups::READ,
                'route_name' => $request->get('_route'),
                'route_params' => $request->query->all(),
                'current_url' => $request->getUri(),
            ],
        );
    }

    #[Route(path: '/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(
        User $user
    ): JsonResponse {

        return $this->json(
            data: $user,
            context: [
                'groups' => UserGroups::READ
            ]
        );
    }

    #[Route('/{id}/tickets', name: 'app_user_ticket_list', methods: ['GET'])]
    public function listTicketByUser(
        User $user,
        #[MapQueryString()] PaginationDto $paginationDto,
        #[MapQueryString()] TicketFiltersDto $ticketFiltersDto,
        Request $request
    ): JsonResponse {

        $filters = array_filter([
            Ticket::FIELD_STATUS => $ticketFiltersDto->status,
            Ticket::FIELD_PRIORITY => $ticketFiltersDto->priority,
        ]);

        $tickets = $this->ticketRepository->findByUser(userId: $user->getId(), paginationDto: $paginationDto, filters: $filters);

        return $this->json(
            data: $tickets,
            status: 200,
            context: [
                'groups' => TicketGroups::READ,
                'route_name' => $request->get('_route'),
                'route_params' => [
                    ...$request->query->all(),
                    ...$request->attributes->get('_route_params'),
                ],
                'current_url' => $request->getUri(),
            ],
        );
    }
}
