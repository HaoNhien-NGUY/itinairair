<?php

namespace App\Model\Trip;

use App\Entity\Trip;

class PlanningViewModel
{
    public function __construct(
        public Trip $trip,
        /** @var PlanningSegmentViewModel[] */
        public array $segments,
        public bool $hasDestinations,
        public bool $startWithTravel,
    ) {
    }
}
