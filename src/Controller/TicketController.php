<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Dto\TicketInputPatchDto;
use App\Dto\TicketInputPostDto;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Constant\TicketGroups;
use App\Entity\User;
use App\Security\Voter\TicketVoter;
use App\Service\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TicketController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private readonly ObjectMapperInterface $objectMapper,
    ) {}

    #[Route('/tickets', name: 'app_ticket_list', methods: ['GET'])]
    public function list(
        #[MapQueryString()] PaginationDto $paginationDto,
        #[MapQueryString()] TicketFiltersDto $ticketFiltersDto,
        Request $request,
    ): JsonResponse {
        $tickets = $this->ticketRepository->getTickets(paginationDto: $paginationDto, ticketFiltersDto: $ticketFiltersDto);

        return $this->json(
            data: $tickets,
            status: 200,
            context: [
                'groups' => TicketGroups::READ,
                'route_name' => $request->get('_route'),
                'route_params' => $request->query->all(),
                'current_url' => $request->getUri(),
            ],
        );
    }

    #[IsGranted(TicketVoter::SHOW, 'ticket')]
    #[Route('/tickets/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(
        Ticket $ticket
    ): JsonResponse {
        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 200);
    }

    #[Route('/tickets', name: 'app_ticket_create', methods: ['POST'])]
    #[IsGranted(TicketVoter::CREATE)]
    public function create(
        CurrentUserProvider $currentUserProvider,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload()] TicketInputPostDto $ticketDto,
    ): JsonResponse {

        $ticket = new Ticket();
        $ticket->setUser($currentUserProvider());

        $this->objectMapper->map(source: $ticketDto, target: $ticket);

        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 201);
    }

    #[Route('/tickets/{id}', name: 'app_ticket_update', methods: ['PATCH'])]
    #[IsGranted(TicketVoter::EDIT, 'ticket')]
    public function update(
        Ticket $ticket,
        #[MapRequestPayload()] TicketInputPatchDto $ticketDto,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        $this->objectMapper->map($ticketDto, $ticket);

        $entityManager->flush();

        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 200);
    }

    #[IsGranted(TicketVoter::DELETE, 'ticket')]
    #[Route('/tickets/{id}', name: 'app_ticket_delete', methods: ['DELETE'])]
    public function delete(
        Ticket $ticket,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        $entityManager->remove($ticket);
        $entityManager->flush();

        return $this->json(data: null, status: 204);
    }

    #[Route('/users/{user_id}/tickets', name: 'app_user_ticket_list', methods: ['GET'])]
    public function listTicketByUser(
        #[MapEntity(id: 'user_id')] User $user,
        #[MapQueryString()] PaginationDto $paginationDto,
        #[MapQueryString()] TicketFiltersDto $ticketFiltersDto,
        Request $request
    ): JsonResponse {
        $tickets = $this->ticketRepository->findByUser(userId: $user->getId(), paginationDto: $paginationDto, ticketFiltersDto: $ticketFiltersDto);

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
