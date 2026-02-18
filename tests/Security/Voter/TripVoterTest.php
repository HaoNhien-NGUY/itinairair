<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Entity\User;
use App\Enum\TripRole;
use App\Security\Voter\TripVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TripVoterTest extends TestCase
{
    #[DataProvider('provideVotingScenarios')]
    public function testVote(string $attribute, ?TripRole $role, int $expectedVote): void
    {
        $voter = new TripVoter();

        $defaultAdmin = new User();
        $user = new User();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $trip = Trip::create($defaultAdmin);

        if ($role) {
            $membership = new TripMembership($trip, $user, $role);

            $trip->addTripMembership($membership);
        }

        $vote = $voter->vote($token, $trip, [$attribute]);

        $this->assertEquals($expectedVote, $vote);
    }

    public static function provideVotingScenarios(): \Generator
    {
        yield ['TRIP_MANAGE', TripRole::ADMIN, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_MANAGE', TripRole::EDITOR, VoterInterface::ACCESS_DENIED];
        yield ['TRIP_MANAGE', TripRole::VIEWER, VoterInterface::ACCESS_DENIED];
        yield ['TRIP_MANAGE', null, VoterInterface::ACCESS_DENIED];

        yield ['TRIP_EDIT', TripRole::ADMIN, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_EDIT', TripRole::EDITOR, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_EDIT', TripRole::VIEWER, VoterInterface::ACCESS_DENIED];

        yield ['TRIP_VIEW', TripRole::ADMIN, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_VIEW', TripRole::EDITOR, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_VIEW', TripRole::VIEWER, VoterInterface::ACCESS_GRANTED];
        yield ['TRIP_VIEW', null, VoterInterface::ACCESS_DENIED];
    }

    public function testVoteOnMembershipSubject(): void
    {
        $voter = new TripVoter();
        $defaultAdmin = new User();
        $user = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $trip = Trip::create($defaultAdmin);
        $membership = new TripMembership($trip, $user, TripRole::ADMIN);
        $trip->addTripMembership($membership);

        $vote = $voter->vote($token, $membership, ['TRIP_MANAGE']);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }
}
