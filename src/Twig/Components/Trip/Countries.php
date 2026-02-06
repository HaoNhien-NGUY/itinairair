<?php

namespace App\Twig\Components\Trip;

use App\Entity\Trip;
use App\Repository\DestinationRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Countries
{
    public Trip $trip;

    public function __construct(
        private readonly DestinationRepository $destinationRepository,
    ) {
    }

    /**
     * @return string[]
     * */
    public function getCountries(): array
    {
        return $this->destinationRepository->findDestinationCountriesByTrip($this->trip);
    }
}
