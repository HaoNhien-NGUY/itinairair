<?php

namespace App\Factory;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\TravelItem;
use App\Model\DayView;
use App\Repository\TravelItemRepository;

readonly class DayFactory
{
    public function __construct(
        private TravelItemRepository $travelItemRepository,
    ) {
    }

    /**
     * @param Day $day
     * @param ?array<TravelItem> $travelItems
     * @return DayView
     */
    public function createDayView(Day $day, ?array $travelItems = null): DayView
    {
        if (null === $travelItems) {
            $travelItems = $this->travelItemRepository->findItemsForDay($day);
        }

        foreach ($travelItems as $item) {
            if ($item instanceof Accommodation) {
                $accommodations[] = $item;
            } elseif ($item instanceof Destination) {
                $destinations[] = $item;
            } elseif ($item instanceof Flight) {
                if ($item->getEndDay() !== null && $day->getPosition() === 1) $isTripStart = true;

                if ($item->getEndDay() === null) $flightSameDay[] = $item;
                else if ($item->getStartDay() === $day) $flightStartDay[] = $item;
                else if ($item->getEndDay() === $day) $flightEndDay[] = $item;
            } else {
                $positionable[] = $item;
            }
        }

        return new DayView(
            day: $day,
            accommodations: $accommodations ?? [],
            positionable: $positionable ?? [],
            flightStartDay: $flightStartDay ?? [],
            flightEndDay: $flightEndDay ?? [],
            flightSameDay: $flightSameDay ?? [],
            destinations: $destinations ?? [],
            isTripStart: $isTripStart ?? false,
            isToday: $day->getDate()->format('Y-m-d') === date('Y-m-d'),
        );
    }
}
