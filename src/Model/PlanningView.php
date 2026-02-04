<?php

namespace App\Model;

use App\Entity\Trip;

class PlanningView
{
    public function __construct(
        public Trip $trip,
        public array $segments,
        public bool $hasDestinations,
        public bool $startWithTravel,
    ) {
    }
}
