<?php

namespace App\Controller;

use App\Constant\UserGroups;
use App\Dto\PaginationDto;
use App\Entity\User;
use App\OpenApi\Attribute\ModelResponse;
use App\OpenApi\Attribute\NotFoundResponse;
use App\OpenApi\Attribute\PaginatedResponse;
use App\OpenApi\Attribute\UnauthorizedResponse;
use App\OpenApi\Attribute\ViolationResponse;
use App\OpenApi\Example\UserExamples;
use App\OpenApi\Tags;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: Tags::USERS)]
#[Route('/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[IsGranted(UserVoter::LIST)]
    #[NotFoundResponse()]
    #[UnauthorizedResponse()]
    #[PaginatedResponse(type: User::class, groups: [UserGroups::READ], example: UserExamples::PAGINATED_LIST)]
    #[ViolationResponse()]
    #[Route(path: '', name: 'app_user_list', methods: ['GET'])]
    public function list(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
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

    #[IsGranted(UserVoter::SHOW)]
    #[ModelResponse(type: User::class, groups: [UserGroups::READ], example: UserExamples::SHOW)]
    #[NotFoundResponse()]
    #[UnauthorizedResponse()]
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
