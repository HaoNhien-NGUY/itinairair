<?php

namespace App\Model;

use App\Entity\Destination;
use App\Entity\Trip;

class PlanningSegmentView
{
    /**
     * @param Trip $trip
     * @param Destination|null $destination
     * @param array<DayView> $days
     * @param bool $isStartTravel Define if the trip starts with a multiday travel
     */
    public function __construct(
        public Trip $trip,
        public ?Destination $destination = null,
        public array $days = [],
        public bool $isStartTravel = false,
    ) {
    }
}
