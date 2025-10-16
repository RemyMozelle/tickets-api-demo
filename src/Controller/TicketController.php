<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Service\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/tickets')]
final class TicketController extends AbstractController
{
    public function __construct(
        private readonly ApiResponse $apiResponse,
        private TicketRepository $ticketRepository,
    ) {}

    #[Route('', name: 'app_ticket')]
    public function getTickets(
        #[MapQueryString()] PaginationDto $paginationDto,
        #[MapQueryString()] TicketFiltersDto $ticketFiltersDto
    ): JsonResponse {
        $page = $paginationDto->page;
        $limit = $paginationDto->limit;

        $filters = array_filter([
            Ticket::FIELD_STATUS => $ticketFiltersDto->status,
            Ticket::FIELD_PRIORITY => $ticketFiltersDto->priority,
        ]);

        $tickets = $this->ticketRepository->getTickets(page: $page, limit: $limit, filters: $filters);
        $total = $this->ticketRepository->count($filters);


        return $this->apiResponse->createApiResponse(data: $tickets, page: $page, total: $total, limit: $limit, context: [
            'groups' => [
                'ticket:read',
            ]
        ]);
    }

    #[Route('/{ticket}', name: 'app_ticket_detail', methods: ['GET'])]
    public function getTicket(
        Ticket $ticket
    ): JsonResponse {
        return $this->apiResponse->createApiResponse(data: $ticket, context: [
            'groups' => ['ticket:read']
        ]);
    }

    #[Route('/{ticket}/comments', name: 'app_ticket_comments', methods: ['GET'])]
    public function getComments(
        Ticket $ticket,
        CommentRepository $commentRepository,
        #[MapQueryString()] PaginationDto $paginationDto,
    ): JsonResponse {
        $page = $paginationDto->page;
        $limit = $paginationDto->limit;

        $comments = $commentRepository->getCommentsTicketId(ticketId: $ticket->getId(), paginationDto: $paginationDto);
        $total = $commentRepository->count(['ticket' => $ticket]);

        return $this->apiResponse->createApiResponse(data: $comments, page: $page, total: $total, limit: $limit, context: ['groups' => 'comment:read']);
    }
}
