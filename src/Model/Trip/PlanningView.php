<?php

namespace App\Model\Trip;

use App\Entity\Trip;

class PlanningView
{
    public function __construct(
        public Trip $trip,
        /** @var PlanningSegmentView[] */
        public array $segments,
        public bool $hasDestinations,
        public bool $startWithTravel,
    ) {
    }
}
