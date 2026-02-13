<?php

namespace App\Presenter;

use App\Entity\Trip;
use App\Model\Trip\ItinerarySegmentViewModel;
use App\Model\Trip\ItineraryViewModel;
use App\Service\TripService;

readonly class ItineraryPresenter
{
    public function __construct(
        private TripService $tripService,
    ) {
    }

    public function createItineraryViewModel(Trip $trip): ItineraryViewModel
    {
        $items = $this->tripService->getTripItinerary($trip);
        $tripFirstDay = $trip->getFirstDay();
        $tripLastDay = $trip->getLastDay();

        $segments = [];
        $initialGap = 0;

        if (empty($items)) {
            $initialGap = $tripLastDay ? $tripLastDay->getPosition() - 1 : 0;
        } elseif (($items[0] ?? null)?->getStartDay() !== $tripFirstDay) {
            $initialGap = $items[0]->getStartDay()->getPosition() - 1;
        }

        foreach ($items as $index => $item) {
            $nextItem = $items[$index + 1] ?? null;
            $gap = ($nextItem?->getStartDay()->getPosition() ?: $tripLastDay->getPosition()) - $item->getEndDay()->getPosition();

            $segments[] = new ItinerarySegmentViewModel(
                item: $item,
                nights: $item->getDurationInDays(true),
                gapNextDay: $gap,
            );
        }

        return new ItineraryViewModel(
            trip: $trip,
            tripFirstDay: $tripFirstDay,
            tripLastDay: $tripLastDay,
            segments: $segments,
            initialGap: $initialGap,
        );
    }
}
