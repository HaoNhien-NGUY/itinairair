<?php

namespace App\Factory;

use App\Entity\Destination;
use App\Entity\Trip;
use App\Model\Trip\PlanningSegmentView;
use App\Model\Trip\PlanningView;
use App\Model\Trip\UserTripCollection;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;
use Symfony\Component\Clock\ClockInterface;

readonly class TripFactory
{
    public function __construct(
        private TravelItemRepository $travelItemRepository,
        private DayFactory $dayFactory,
        private DestinationRepository $destinationRepository,
        private FlightRepository $flightRepository,
        private ClockInterface $clock,
    ) {
    }

    public function createPlanningView(Trip $trip): PlanningView
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

    /**
     * @param Trip[] $trips
     */
    public function createUserTripCollection(array $trips): UserTripCollection
    {
        if (empty($trips)) {
            return new UserTripCollection();
        }

        $coming = [];
        $past = [];
        $ongoing = [];

        $now = $this->clock->now()->setTime(0, 0);
        $countriesByTrip = $this->destinationRepository->findDestinationCountriesByTrips($trips);

        foreach ($trips as $trip) {
            $trip->setCountryNames($countriesByTrip[$trip->getId()] ?? []);

            if ($trip->getStartDate() > $now) {
                $trip->setDaysDifferenceFromNow($trip->getStartDate()->diff($now)->days);
                $coming[] = $trip;
            } elseif ($trip->getEndDate() < $now) {
                $trip->setDaysDifferenceFromNow($now->diff($trip->getEndDate())->days);
                $past[] = $trip;
            } else {
                $trip->setDaysDifferenceFromNow($trip->getEndDate()->diff($now)->days);
                $ongoing[] = $trip;
            }
        }

        usort($past, fn ($a, $b) => $b->getEndDate() <=> $a->getEndDate());

        return new UserTripCollection(
            ongoing: $ongoing,
            coming: $coming,
            past: $past,
        );
    }
}
