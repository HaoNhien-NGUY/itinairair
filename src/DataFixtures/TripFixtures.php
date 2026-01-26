<?php

namespace App\DataFixtures;

use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Entity\User;
use App\Enum\TripRole;
use App\Service\TripService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TripFixtures extends Fixture implements DependentFixtureInterface
{
    public const TRIP_PARIS = 'trip_paris';
    public const TITLE_PARIS = 'Summer in Paris 2024';

    public function __construct(private readonly TripService $tripService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $trip = new Trip();

        $trip
            ->setName(self::TITLE_PARIS)
            ->setStartDate(new DateTime('2024-01-01'))
            ->setEndDate(new DateTime('2024-01-05'));

        $this->tripService->addOrRemoveTripDays($trip, $trip->getDurationInDays());

        $membership = new TripMembership(
            $trip,
            $this->getReference(UserFixtures::USER_TRIP_ADMIN, User::class),
            TripRole::ADMIN,
        );

        $trip->addTripMembership($membership);

        $manager->persist($trip);
        $manager->persist($membership);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
