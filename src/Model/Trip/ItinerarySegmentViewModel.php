<?php

namespace App\Model\Trip;

use App\Entity\Destination;
use App\Entity\Flight;

class ItinerarySegmentViewModel
{
    public function __construct(
        public Flight|Destination $item,
        public ?int $nights = null,
        public int $gapNextDay = 0,
    ) {
    }
}
