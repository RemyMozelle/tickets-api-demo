<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\TicketCommentInputPatchDto;
use App\Dto\TicketCommentInputPostDto;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Constant\CommentGroups;
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

final class CommentController extends AbstractController
{
    public function __construct(
        private readonly ObjectMapperInterface $objectMapper,
    ) {}

    #[Route('/comments/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(
        Comment $comment,
        CommentRepository $commentRepository,
    ): JsonResponse {
        $comment = $commentRepository->find($comment->getId());

        return $this->json(data: $comment, context: ['groups' => CommentGroups::READ], status: 200);
    }

    #[Route('/comments/{id}', name: 'app_comment_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function patch(
        EntityManagerInterface $entityManager,
        Comment $comment,
        #[MapRequestPayload(acceptFormat: 'json')] TicketCommentInputPatchDto $ticketCommentDto,
    ): JsonResponse {

        $this->objectMapper->map(source: $ticketCommentDto, target: $comment);

        $entityManager->flush();

        return $this->json(data: $comment, context: ['groups' => CommentGroups::READ], status: 200);
    }

    #[Route('/comments/{id}', name: 'app_comment_delete', methods: ['DELETE'])]
    public function delete(
        Comment $comment,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(data: null, status: 204);
    }

    #[Route('/tickets/{ticket_id}/comments', name: 'app_ticket_comment_list', methods: ['GET'])]
    public function listCommentByTicket(
        #[MapEntity(id: 'ticket_id')] Ticket $ticket,
        CommentRepository $commentRepository,
        #[MapQueryString()] PaginationDto $paginationDto,
        Request $request,
    ): JsonResponse {

        $comments = $commentRepository->getCommentsTicketId(ticketId: $ticket->getId(), paginationDto: $paginationDto);

        return $this->json(
            data: $comments,
            context: [
                'groups' => CommentGroups::READ,
                'route_name' => $request->get('_route'),
                'route_params' => [
                    ...$request->query->all(),
                    ...$request->attributes->get('_route_params')
                ],
                'current_url' => $request->getUri(),
            ],
            status: 200
        );
    }

    #[Route('/tickets/{ticket_id}/comments', name: 'app_ticket_comment_create', methods: ['POST'])]
    public function createCommentForTicket(
        Security $security,
        EntityManagerInterface $entityManager,
        #[MapEntity(id: 'ticket_id')]
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

        return $this->json(data: $comment, context: ['groups' => CommentGroups::READ], status: 201);
    }

    #[Route('/users/{user_id}/comments', name: 'app_user_comments_list', methods: 'GET')]
    public function listCommentByUser(
        #[MapEntity(id: 'user_id')]
        User $user,
        #[MapQueryString()] PaginationDto $paginationDto,
        Request $request,
        CommentRepository $commentRepository
    ): JsonResponse {
        $comments = $commentRepository->findByUser(userId: $user->getId(), paginationDto: $paginationDto);

        return $this->json(
            data: $comments,
            status: 200,
            context: [
                'groups' => CommentGroups::READ,
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
