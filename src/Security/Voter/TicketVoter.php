<?php

namespace App\Security\Voter;

use App\Constant\Roles;
use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Ticket>
 */
final class TicketVoter extends Voter
{
    public const CREATE = 'TICKET_CREATE';

    public const EDIT = 'TICKET_EDIT';

    public const SHOW = 'TICKET_VIEW';

    public const DELETE = 'TICKET_DELETE';

    public const LIST = 'TICKET_LIST';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return match ($attribute) {
            self::LIST,
            self::CREATE => true,
            self::EDIT,
            self::SHOW,
            self::DELETE => $subject instanceof Ticket,
            default => false,
        };
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->accessDecisionManager->decide($token, [Roles::ROLE_ADMIN])) {
            return true;
        }

        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::LIST,
            self::CREATE,
            self::SHOW => true,
            self::EDIT,
            self::DELETE => $this->isAllowedForTicket($subject, $user, $attribute),
            default => false
        };
    }

    private function canEdit(Ticket $ticket, User $user): bool
    {
        if ($user === $ticket->getUser()) {
            return true;
        }

        return false;
    }

    private function canDelete(Ticket $ticket, User $user): bool
    {
        if ($user === $ticket->getUser()) {
            return true;
        }

        return false;
    }

    private function isAllowedForTicket(Ticket $ticket, User $user, string $attribute): bool
    {
        return match ($attribute) {
            self::EDIT => $this->canEdit($ticket, $user),
            self::DELETE => $this->canDelete($ticket, $user),
            default => false
        };
    }
}
