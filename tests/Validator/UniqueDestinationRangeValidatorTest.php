<?php

namespace App\Tests\Validator;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use App\Validator\UniqueDestinationRange;
use App\Validator\UniqueDestinationRangeValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueDestinationRangeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): UniqueDestinationRangeValidator
    {
        return new UniqueDestinationRangeValidator();
    }

    private function createDay(Trip $trip, int $position): Day
    {
        $day = new Day();
        $day->setTrip($trip);
        $day->setPosition($position);
        return $day;
    }

    public function testNoOverlap()
    {
        $trip = new Trip();

        $d1 = new Destination($trip);
        $d1->setStartDay($this->createDay($trip, 1));
        $d1->setEndDay($this->createDay($trip, 6));

        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 6));
        $d2->setEndDay($this->createDay($trip, 12));
        $trip->addTravelItem($d1);
        $trip->addTravelItem($d2);
        $this->validator->validate($d2, new UniqueDestinationRange());

        $this->assertNoViolation();
    }

    public function testOverlap()
    {
        $trip = new Trip();

        $d1 = new Destination($trip);
        $d1->setStartDay($this->createDay($trip, 1));
        $d1->setEndDay($this->createDay($trip, 7));

        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 6));
        $d2->setEndDay($this->createDay($trip, 12));
        $trip->addTravelItem($d1);
        $trip->addTravelItem($d2);
        $this->validator->validate($d2, new UniqueDestinationRange());

        $this->buildViolation('Les dates de cette destination chevauchent une autre destination de ce voyage.')
            ->atPath('property.path.startDay')
            ->assertRaised();
    }

    public function testOverlapReverseOrder()
    {
        $trip = new Trip();

        $d1 = new Destination($trip);
        $d1->setStartDay($this->createDay($trip, 6));
        $d1->setEndDay($this->createDay($trip, 12));

        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 1));
        $d2->setEndDay($this->createDay($trip, 7));
        $trip->addTravelItem($d1);
        $trip->addTravelItem($d2);
        $this->validator->validate($d2, new UniqueDestinationRange());

        $this->buildViolation('Les dates de cette destination chevauchent une autre destination de ce voyage.')
            ->atPath('property.path.startDay')
            ->assertRaised();
    }

    public function testContainedOverlap()
    {
        $trip = new Trip();

        $d1 = new Destination($trip);
        $d1->setStartDay($this->createDay($trip, 1));
        $d1->setEndDay($this->createDay($trip, 10));

        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 3));
        $d2->setEndDay($this->createDay($trip, 5));
        $trip->addTravelItem($d1);
        $trip->addTravelItem($d2);
        $this->validator->validate($d2, new UniqueDestinationRange());

        $this->buildViolation('Les dates de cette destination chevauchent une autre destination de ce voyage.')
            ->atPath('property.path.startDay')
            ->assertRaised();
    }
}
