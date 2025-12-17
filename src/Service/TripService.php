<?php

namespace App\Service;

use App\Entity\Trip;
use App\Repository\DestinationRepository;

class TripService
{
    public function __construct(private DestinationRepository $destinationRepository) {
    }

    /**
     * @param Trip $trip
     * @return array
     */
    public function getTripStatistics(Trip $trip): array
    {
        $countries = $this->destinationRepository->findDestinationCountriesByTrip($trip);
        $cities = $this->destinationRepository->findDestinationCitiesByTrip($trip);

        $stats = [
            'duration' => $trip->getDays()->count(),
            'countries' => $countries,
            'cities' => $cities,
            'country_count' => count($countries),
            'city_count' => count($cities),

        ];

        return $stats;
    }
}
