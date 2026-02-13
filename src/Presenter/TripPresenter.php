<?php

namespace App\Presenter;

use App\Entity\Destination;
use App\Entity\Trip;
use App\Model\Trip\PlanningSegmentViewModel;
use App\Model\Trip\PlanningViewModel;
use App\Model\Trip\TripListViewModel;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;
use Symfony\Component\Clock\ClockInterface;

readonly class TripPresenter
{
    public function __construct(
        private TravelItemRepository $travelItemRepository,
        private DayPresenter $dayFactory,
        private DestinationRepository $destinationRepository,
        private FlightRepository $flightRepository,
        private ClockInterface $clock,
    ) {
    }

    public function createPlanningViewModel(Trip $trip): PlanningViewModel
    {
        $groupedDestinations = $this->destinationRepository->findDestinationsMappedByDayPosition($trip);
        $items = $this->travelItemRepository->findItemDayPairsForTrip($trip);
        $flightTripStart = $this->flightRepository->countFirstDayOverNightFlight($trip);

        $days = $trip->getDays();
        $segments = [];

        $prevDestinationId = 'INIT';
        /** @var Destination|null $currentDestination */
        $currentDestination = null;
        /** @var PlanningSegmentViewModel|null $currentSegment */
        $currentSegment = null;

        foreach ($days as $day) {
            $position = $day->getPosition();

            if (isset($groupedDestinations['byStartDay'][$position])) {
                $currentDestination = $groupedDestinations['byStartDay'][$position];
            }

            $currentDestId = $currentDestination?->getId();

            if ($prevDestinationId !== $currentDestId) {
                $currentSegment =  new PlanningSegmentViewModel(
                    trip: $trip,
                    destination: $currentDestination,
                    days: [],
                );

                $segments[] = $currentSegment;
                $prevDestinationId = $currentDestId;
            }

            if ($currentSegment) {
                $currentSegment->days[] = $this->dayFactory->createDayViewModel(
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

        return new PlanningViewModel(
            trip: $trip,
            segments: $segments,
            hasDestinations: !empty($groupedDestinations['byStartDay']),
            startWithTravel: $flightTripStart > 0,
        );
    }

    /**
     * @param Trip[] $trips
     */
    public function createTripListViewModel(array $trips): TripListViewModel
    {
        if (empty($trips)) {
            return new TripListViewModel();
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

        return new TripListViewModel(
            ongoing: $ongoing,
            coming: $coming,
            past: $past,
        );
    }
}
