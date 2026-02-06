<?php

namespace App\Twig\Components\Trip\Dashboard;

use App\Entity\Trip;
use App\Entity\User;
use App\Factory\TripFactory;
use App\Model\Trip\UserTripCollection;
use App\Repository\TripRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class UserTrips
{
    public Trip $trip;

    public User $user;

    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly TripFactory $tripFactory,
    ) {
    }

    public function getUserTripCollection(): UserTripCollection
    {
        $trips = $this->tripRepository->findByUser($this->user);

        return $this->tripFactory->createUserTripCollection($trips);
    }
}
