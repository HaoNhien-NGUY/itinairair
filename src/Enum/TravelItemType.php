<?php


namespace App\Enum;

use App\Entity\Accommodation;
use App\Entity\Activity;
use App\Entity\Flight;
use App\Entity\TravelItem;
use App\Form\TravelItem\AccommodationType;
use App\Form\TravelItem\ActivityType;
use App\Form\TravelItem\FlightType;

enum TravelItemType: string
{
    case ACCOMMODATION = 'accommodation';
    case FLIGHT = 'flight';
    case ACTIVITY = 'activity';

    public function getFormType(): string
    {
        return match ($this) {
            self::ACCOMMODATION => AccommodationType::class,
            self::FLIGHT => FlightType::class,
            self::ACTIVITY => ActivityType::class,
        };
    }

    public function getClass(): string
    {
        return match($this) {
            self::ACCOMMODATION => Accommodation::class,
            self::FLIGHT => Flight::class,
            self::ACTIVITY => Activity::class,
        };
    }

    public function createInstance(array $params = []): TravelItem
    {
        return new ($this->getClass())($params);
    }
}
