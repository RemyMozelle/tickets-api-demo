<?php

namespace App\Controller\Ticket;

use App\Dto\PaginationDto;
use App\Dto\TicketCommentInputPatchDto;
use App\Dto\TicketCommentInputPostDto;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tickets/{id}/comments')]
final class TicketCommentController extends AbstractController
{
    public function __construct(
        private readonly ObjectMapperInterface $objectMapper,
    ) {}

    #[Route('', name: 'app_ticket_comment_index', methods: ['GET'])]
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
                'route_name' => 'app_ticket_comment_index',
                'route_params' => [
                    ...$request->query->all(),
                    ...$request->attributes->get('_route_params')
                ],
                'current_url' => $request->getUri(),
            ],
            status: 200
        );
    }

    #[Route('', name: 'app_ticket_comment_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Security $security,
        EntityManagerInterface $entityManager,
        Ticket $ticket,
        #[MapRequestPayload(acceptFormat: 'json')] TicketCommentInputPostDto $ticketCommentDto,
    ): JsonResponse {

        $user = $security->getUser();

        $comment = new Comment();
        $comment
            ->setUser($user)
            ->setTicket($ticket);

        $this->objectMapper->map(source: $ticketCommentDto, target: $comment);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json(data: $comment, context: ['groups' => 'comment:read'], status: 201);
    }

    #[Route('/{comment_id}', name: 'app_ticket_comment_show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['id' => 'ticket', 'comment_id' => 'id'])] Comment $comment,
        CommentRepository $commentRepository,
    ): JsonResponse {
        $comment = $commentRepository->find($comment->getId());

        return $this->json(data: $comment, context: ['groups' => 'comment:read'], status: 200);
    }

    #[Route('/{comment_id}', name: 'app_ticket_comment_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ['id' => 'ticket', 'comment_id' => 'id'])] Comment $comment,
        #[MapRequestPayload(acceptFormat: 'json')] TicketCommentInputPatchDto $ticketCommentDto,
    ): JsonResponse {

        $this->objectMapper->map(source: $ticketCommentDto, target: $comment);

        $entityManager->flush();

        return $this->json(data: $comment, context: ['groups' => 'comment:read'], status: 200);
    }

    #[Route('/{comment_id}', name: 'app_ticket_comment_delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(mapping: ['id' => 'ticket', 'comment_id' => 'id'])] Comment $comment,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(data: null, status: 204);
    }
}
