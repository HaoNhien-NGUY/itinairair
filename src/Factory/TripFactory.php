<?php

namespace App\Factory;

use App\Entity\Destination;
use App\Entity\Trip;
use App\Model\PlanningSegmentView;
use App\Model\PlanningView;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;

readonly class TripFactory
{
    public function __construct(
        private TravelItemRepository $travelItemRepository,
        private DayFactory $dayFactory,
        private DestinationRepository $destinationRepository,
        private FlightRepository $flightRepository,
    ) {
    }

    public function planningView(Trip $trip): PlanningView
    {
        $groupedDestinations = $this->destinationRepository->findDestinationsMappedByDayPosition($trip);
        $items = $this->travelItemRepository->findItemDayPairsForTrip($trip);
        $flightTripStart = $this->flightRepository->countFirstDayOverNightFlight($trip);

        $days = $trip->getDays();
        $segments = [];

        $prevDestinationId = 'INIT';
        /** @var Destination|null $currentDestination */
        $currentDestination = null;
        /** @var PlanningSegmentView|null $currentSegment */
        $currentSegment = null;

        foreach ($days as $day) {
            $position = $day->getPosition();

            if (isset($groupedDestinations['byStartDay'][$position])) {
                $currentDestination = $groupedDestinations['byStartDay'][$position];
            }

            $currentDestId = $currentDestination?->getId();

            if ($prevDestinationId !== $currentDestId) {
                $currentSegment =  new PlanningSegmentView(
                    trip: $trip,
                    destination: $currentDestination,
                    days: [],
                );

                $segments[] = $currentSegment;
                $prevDestinationId = $currentDestId;
            }

            if ($currentSegment) {
                $currentSegment->days[] = $this->dayFactory->createDayView(
                    day: $day,
                    travelItems: $items[$day->getId()] ?? [],
                );
            }

            if (null !== $currentDestId
                && isset($groupedDestinations['byEndDay'][$position])
                && $groupedDestinations['byEndDay'][$position]->getId() === $currentDestId
            ) {
                $currentDestination = null;
            }
        }

        return new PlanningView(
            trip: $trip,
            segments: $segments,
            hasDestinations: !empty($groupedDestinations['byStartDay']),
            startWithTravel: $flightTripStart > 0,
        );
    }
}
