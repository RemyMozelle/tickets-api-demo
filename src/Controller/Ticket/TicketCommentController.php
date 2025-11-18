<?php

namespace App\Controller\Ticket;

use App\Dto\PaginationDto;
use App\Entity\Ticket;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tickets')]
final class TicketCommentController extends AbstractController
{
    #[Route('/{ticket}/comments', name: 'app_ticket_comments_index', methods: ['GET'])]
    public function index(
        Ticket $ticket,
        CommentRepository $commentRepository,
        #[MapQueryString()] PaginationDto $paginationDto,
        HttpFoundationRequest $request,
    ): JsonResponse {
        $comments = $commentRepository->getCommentsTicketId(ticketId: $ticket->getId(), paginationDto: $paginationDto);

        return $this->json(
            data: $comments,
            context: [
                'groups' => 'comment:read',
                'route_name' => 'app_ticket_comments_index',
                'route_params' => [
                    ...$request->query->all(),
                    'ticket' => $ticket->getId()
                ],
                'current_url' => $request->getUri(),
            ],
            status: 200
        );
    }
}
