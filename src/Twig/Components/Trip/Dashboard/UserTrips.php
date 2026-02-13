<?php

namespace App\Twig\Components\Trip\Dashboard;

use App\Entity\Trip;
use App\Entity\User;
use App\Model\Trip\TripListViewModel;
use App\Presenter\TripPresenter;
use App\Repository\TripRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class UserTrips
{
    public Trip $trip;

    public User $user;

    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly TripPresenter $tripFactory,
    ) {
    }

    public function getTripList(): TripListViewModel
    {
        $trips = $this->tripRepository->findByUser($this->user);

        return $this->tripFactory->createTripListViewModel($trips);
    }
}
