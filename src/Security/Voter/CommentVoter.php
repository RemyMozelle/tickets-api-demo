<?php

namespace App\Security\Voter;

use App\Constant\Roles;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoter extends Voter
{
    public const CREATE = 'COMMENT_CREATE';
    public const EDIT = 'COMMENT_EDIT';
    public const SHOW = 'COMMENT_VIEW';
    public const DELETE = 'COMMENT_DELETE';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return match ($attribute) {
            self::CREATE,
            self::SHOW => true,
            self::EDIT, 
            self::DELETE => $subject instanceof Comment,
            default => false,
        };
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->accessDecisionManager->decide($token, [Roles::ROLE_ADMIN])) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::CREATE,
            self::SHOW => true,
            self::EDIT,
            self::DELETE => $subject instanceof Comment && $this->isAllowedForComment($subject, $user, $attribute),
            default => false
        };
    }

    private function canEdit(Comment $Comment, User $user): bool
    {
        if ($user === $Comment->getUser()) {
            return true;
        }

        return false;
    }

    private function canDelete(Comment $Comment, User $user): bool
    {
        if ($user === $Comment->getUser()) {
            return true;
        }

        return false;
    }

    private function isAllowedForComment(Comment $Comment, User $user, string $attribute)
    {
        return match ($attribute) {
            self::EDIT => $this->canEdit($Comment, $user),
            self::DELETE => $this->canDelete($Comment, $user),
            default => false
        };
    }
}
