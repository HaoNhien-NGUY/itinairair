<?php

namespace App\Service;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;

readonly class TripService
{
    public function __construct(
        private DestinationRepository $destinationRepository,
        private FlightRepository      $flightRepository,
    ) {
    }

    /**
     * @param Trip $trip
     * @return array{duration: int, countries: string[], cities: string[], country_count: int, city_count: int}
     */
    public function getTripStatistics(Trip $trip): array
    {
        $countries = $this->destinationRepository->findDestinationCountriesByTrip($trip);
        $cities = $this->destinationRepository->findDestinationCitiesByTrip($trip);

        return [
            'duration' => $trip->getDays()->count(),
            'countries' => $countries,
            'cities' => $cities,
            'country_count' => count($countries),
            'city_count' => count($cities),
        ];
    }

    /**
     * @return array<Flight|Destination>
     */
    public function getTripItinerary(Trip $trip): array
    {
        $destinations = $this->destinationRepository->findDestinationByTrip($trip);
        $flights = $this->flightRepository->findOverNightFlightsByTrip($trip);

        $merged = array_merge($destinations, $flights);
        usort($merged, fn(TravelItem $a, TravelItem  $b) => $a->getStartDay()->getPosition() <=> $b->getStartDay()->getPosition());

        return $merged;
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
