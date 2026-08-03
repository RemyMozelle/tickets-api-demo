<?php

namespace App\Controller;

use App\Constant\UserGroups;
use App\Dto\PaginationDto;
use App\Entity\User;
use App\Repository\UserRepository;
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
    ) {
    }

    #[Route(path: '', name: 'app_user_list', methods: ['GET'])]
    public function list(
        #[MapQueryString()]
        PaginationDto $paginationDto,
        Request $request,
    ): JsonResponse {
        $users = $this->userRepository->getUsers($paginationDto);

        return $this->json(
            data: $users,
            status: 200,
            context: [
                'groups' => UserGroups::READ,
                'route_name' => $request->attributes->get('_route'),
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
                'groups' => UserGroups::READ,
            ]
        );
    }
}
