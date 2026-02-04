<?php

namespace App\Factory;

use App\Entity\Trip;
use App\Model\PlanningSegmentView;
use App\Model\PlanningView;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;

readonly class TripFactory
{
    public function __construct(
        private TravelItemRepository    $travelItemRepository,
        private DayFactory              $dayFactory,
        private DestinationRepository   $destinationRepository,
        private FlightRepository        $flightRepository,
    ) {
    }

    public function planningView(Trip $trip): PlanningView
    {
        $groupedDestinations = $this->destinationRepository->findDestinationsMappedByDayPosition($trip);
        $items = $this->travelItemRepository->findItemDayPairsForTrip($trip);
        $flightTripStart = $this->flightRepository->countFirstDayOverNightFlight($trip);
        $days = $trip->getDays();
        $nbDays = count($days);

        $segments = [];
        $currentDestination = null;
        $prevDestination = 'INIT';

        foreach ($days as $day) {
            if (isset($groupedDestinations['byStartDay'][$day->getPosition()])) {
                $currentDestination = $groupedDestinations['byStartDay'][$day->getPosition()];
            }

            if ($prevDestination !== $currentDestination) {
                $segments[] = new PlanningSegmentView(
                    trip: $trip,
                    destination: $currentDestination,
                    days: [],
                );
            }

            $segments[array_key_last($segments)]->days[] = $this->dayFactory->dayView(
                day: $day,
                travelItems: $items[$day->getId()] ?? [],
            );

            $prevDestination = $currentDestination;

            if (($day->getPosition() + 1 < $nbDays) && isset($groupedDestinations['byEndDay'][$day->getPosition() + 1])) {
                $currentDestination = null;
            }
        }

        return new PlanningView(
            trip: $trip,
            segments: $segments,
            hasDestinations: !empty($groupedDestinations['all']),
            startWithTravel: $flightTripStart,
        );
    }
}
