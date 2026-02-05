<?php

namespace App\Factory;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Enum\TravelItemType;
use App\Model\DayView;
use App\Repository\TravelItemRepository;

class DayFactory
{
    public function __construct(
        private readonly TravelItemRepository $travelItemRepository,
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
            switch ($item->getItemType()) {
                case TravelItemType::ACCOMMODATION:
                    $accommodations[] = $item;
                    break;
                case TravelItemType::DESTINATION:
                    $destinations[] = $item;
                    break;
                case TravelItemType::FLIGHT:
                    if ($item->getEndDay() !== null && $day->getPosition() === 1) $isTripStart = true;

                    if ($item->getEndDay() === null) $flightSameDay[] = $item;
                    else if ($item->getStartDay() === $day) $flightStartDay[] = $item;
                    else if ($item->getEndDay() === $day) $flightEndDay[] = $item;

                    break;
                default:
                    $positionable[] = $item;
                    break;
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
