<?php

namespace App\Model\Trip;

use App\Entity\Trip;

final class TripListViewModel
{
    /**
     * @param Trip[]               $ongoing
     * @param Trip[]               $coming
     * @param Trip[]               $past
     * @param array<int, string[]> $countriesByTrip
     */
    public function __construct(
        public array $ongoing = [],
        public array $coming = [],
        public array $past = [],
        public array $countriesByTrip = [],
    ) {
    }

    public function getCount(): int
    {
        return count($this->ongoing) + count($this->coming) + count($this->past);
    }
}
