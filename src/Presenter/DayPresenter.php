<?php

namespace App\Presenter;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\TravelItem;
use App\Model\Trip\DayViewModel;
use App\Repository\TravelItemRepository;

readonly class DayPresenter
{
    public function __construct(
        private TravelItemRepository $travelItemRepository,
    ) {
    }

    /**
     * @param ?array<TravelItem> $travelItems
     */
    public function createDayViewModel(Day $day, ?array $travelItems = null): DayViewModel
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
                if (null !== $item->getEndDay() && 1 === $day->getPosition()) {
                    $isTripStart = true;
                }

                if (null === $item->getEndDay()) {
                    $flightSameDay[] = $item;
                } elseif ($item->getStartDay() === $day) {
                    $flightStartDay[] = $item;
                } elseif ($item->getEndDay() === $day) {
                    $flightEndDay[] = $item;
                }
            } else {
                $positionable[] = $item;
            }
        }

        return new DayViewModel(
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
