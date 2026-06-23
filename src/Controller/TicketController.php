<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Dto\TicketInputPatchDto;
use App\Dto\TicketInputPostDto;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Constant\TicketGroups;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tickets')]
final class TicketController extends AbstractController
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private readonly ObjectMapperInterface $objectMapper,
    ) {}

    #[Route('', name: 'app_ticket_list', methods: ['GET'])]
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

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(
        Ticket $ticket
    ): JsonResponse {
        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 200);
    }

    #[Route('', name: 'app_ticket_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Security $security,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload()] TicketInputPostDto $ticketDto,
    ): JsonResponse {
        $user = $security->getUser();

        $ticket = new Ticket();
        $ticket->setUser($user);

        $this->objectMapper->map(source: $ticketDto, target: $ticket);

        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 201);
    }

    #[Route('/{id}', name: 'app_ticket_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function update(
        Ticket $ticket,
        #[MapRequestPayload()] TicketInputPatchDto $ticketDto,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $this->objectMapper->map($ticketDto, $ticket);

        $entityManager->flush();

        return $this->json(data: $ticket, context: ['groups' => TicketGroups::READ], status: 200);
    }

    #[Route('/{id}', name: 'app_ticket_delete', methods: ['DELETE'])]
    public function delete(
        Ticket $ticket,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        $entityManager->remove($ticket);
        $entityManager->flush();

        return $this->json(data: null, status: 204);
    }
}
