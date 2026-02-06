<?php

namespace App\Model\Trip;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\TravelItem;

class DayView
{
    /**
     * @param Accommodation[] $accommodations
     * @param TravelItem[]    $positionable
     * @param Flight[]        $flightStartDay
     * @param Flight[]        $flightEndDay
     * @param Flight[]        $flightSameDay
     * @param Destination[]   $destinations
     */
    public function __construct(
        public Day $day,
        public array $accommodations,
        public array $positionable,
        public array $flightStartDay,
        public array $flightEndDay,
        public array $flightSameDay,
        public array $destinations,
        public bool $isTripStart,
        public bool $isToday,
    ) {
    }
}
