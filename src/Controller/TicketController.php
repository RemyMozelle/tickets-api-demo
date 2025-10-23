<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketFiltersDto;
use App\Entity\Ticket;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Service\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
        [$tickets, $count] = $this->ticketRepository->getTickets(paginationDto: $paginationDto, ticketFiltersDto: $ticketFiltersDto);

        return $this->apiResponse->createApiResponseWithPagination(
            data: $tickets,
            paginationDto: $paginationDto,
            total: $count,
            context: [
                'groups' => [
                    'ticket:read',
                ]
            ]
        );
    }

    #[Route('/{ticket}', name: 'app_ticket_detail', methods: ['GET'])]
    public function getTicket(
        Ticket $ticket
    ): JsonResponse {
        return $this->apiResponse->createApiResponse(
            data: $ticket, 
            context: [
            'groups' => ['ticket:read']
        ]);
    }

    #[Route('/{ticket}/comments', name: 'app_ticket_comments', methods: ['GET'])]
    public function getComments(
        Ticket $ticket,
        CommentRepository $commentRepository,
        #[MapQueryString()] PaginationDto $paginationDto,
    ): JsonResponse {

        $comments = $commentRepository->getCommentsTicketId(ticketId: $ticket->getId(), paginationDto: $paginationDto);
        $total = $commentRepository->count(['ticket' => $ticket]);

        return $this->apiResponse->createApiResponseWithPagination(
            data: $comments,
            paginationDto: $paginationDto,
            total: $total,
            context: [
                'groups' => 'comment:read'
            ]
        );
    }
}
