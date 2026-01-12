<?php

namespace App\Service;

use App\Entity\Day;
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

    public function addOrRemoveTripDays(Trip $trip, int $requiredCount): void
    {
        if (!$trip->getStartDate() || !$trip->getEndDate()) {
            return;
        }

        $currentDays = $trip->getDays();
        $currentCount = count($currentDays);

        if ($currentCount > $requiredCount) {
            $toRemove = [];

            foreach ($currentDays as $day) {
                if ($day->getPosition() > $requiredCount) {
                    $toRemove[] = $day;
                }
            }

            foreach ($toRemove as $day) {
                $trip->removeDay($day);
            }
        } elseif ($currentCount < $requiredCount) {
            for ($i = $currentCount; $i < $requiredCount; $i++) {
                $newDay = new Day();
                $newDay->setPosition($i + 1);
                $trip->addDay($newDay);
            }
        }

        foreach ($trip->getDays() as $day) {
            $day->setDate($day->getComputedDate());
        }
    }
}
