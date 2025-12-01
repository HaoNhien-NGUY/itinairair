<?php

namespace App\Security\Voter;

use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Enum\TripRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class TripVoter extends Voter
{
    public const EDIT = 'TRIP_EDIT';
    public const VIEW = 'TRIP_VIEW';
    public const MANAGE = 'TRIP_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::MANAGE])
            && ($subject instanceof Trip || $subject instanceof TripMembership);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var TripMembership|Trip $subject */
        $membership = null;

        if ($subject instanceof TripMembership) {
            $membership = $subject;
        } elseif ($subject instanceof Trip) {
            $membership = $subject->getMembershipForUser($user);
        }

        if (!$membership) {
            return false;
        }

        return match ($attribute) {
            self::MANAGE => $this->canManage($membership),
            self::EDIT => $this->canEdit($membership),
            self::VIEW => $this->canView($membership),
            default => false,
        };

    }

    private function canView(TripMembership $membership): bool
    {
        return $this->canEdit($membership)
            || $membership->getRole() == TripRole::VIEWER;
    }

    private function canEdit(TripMembership $membership): bool
    {
        return $this->canManage($membership)
            || $membership->getRole() == TripRole::EDITOR;
    }

    private function canManage(TripMembership $membership): bool
    {
        return $membership->getRole() == TripRole::ADMIN;
    }
}
