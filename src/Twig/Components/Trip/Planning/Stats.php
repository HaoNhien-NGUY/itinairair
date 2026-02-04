<?php

namespace App\Twig\Components\Trip\Planning;

use App\Entity\Trip;
use App\Repository\AccommodationRepository;
use App\Repository\FlightRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Stats
{
    public Trip $trip;

    public function __construct(
        private readonly AccommodationRepository $accommodationRepository,
        private readonly FlightRepository $flightRepository,
    ) {
    }

    public function getAccommodationCount(): int
    {
        return $this->accommodationRepository->countAccommodationsByTrip($this->trip);
    }

    public function getFlightCount(): int
    {
        return $this->flightRepository->countFlightsByTrip($this->trip);
    }
}
