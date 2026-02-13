<?php

namespace App\Model\Trip;

use App\Entity\Day;
use App\Entity\Trip;

class ItineraryViewModel
{
    public function __construct(
        public Trip $trip,
        public Day $tripFirstDay,
        public Day $tripLastDay,
        /** @var ItinerarySegmentViewModel[] */
        public array $segments,
        public int $initialGap = 0,
    ) {
    }
}
