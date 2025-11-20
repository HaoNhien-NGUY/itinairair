<?php

namespace App\Service;

use App\Entity\Trip;

class TripService
{
    public function __construct() {
    }

    /**
     * @param Trip $trip
     * @param array $accommodations
     * @return array
     */
    public function getTripStatistics(Trip $trip, array $accommodations): array
    {
        $stats = ['duration' => $trip->getDays()->count()];
        $countries = [];
        $cities = [];

        foreach ($accommodations as $accommodation) {
            if ($accommodation->getPlace()) {
                $address = $accommodation->getPlace()->getAddress();
            }

            if (!empty($address)) {
                $parts = array_map('trim', explode(',', $address));
                $count = count($parts);

                if ($count >= 1) {
                    $country = $parts[$count - 1];
                    $countries[$country] = true;

                    if ($count >= 2) {
                        // "..., City, Country"
                        $city = $parts[$count - 2] . ', ' . $country;
                        $cities[$city] = true;
                    }
                }
            }
        }

        $stats['countries'] = array_keys($countries);
        $stats['country_count'] = count($countries);
        $stats['city_count'] = count($cities);

        return $stats;
    }
}
