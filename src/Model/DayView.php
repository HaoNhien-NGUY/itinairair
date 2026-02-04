<?php

namespace App\Model;

use App\Entity\Day;

class DayView
{
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
