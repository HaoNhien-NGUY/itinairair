<?php

namespace App\Tests\Validator;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use App\Repository\DestinationRepository;
use App\Validator\UniqueDestinationRange;
use App\Validator\UniqueDestinationRangeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<UniqueDestinationRangeValidator>
 */
class UniqueDestinationRangeValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject $repositoryMock;

    protected function createValidator(): UniqueDestinationRangeValidator
    {
        $this->repositoryMock = $this->createMock(DestinationRepository::class);

        return new UniqueDestinationRangeValidator($this->repositoryMock);
    }

    private function createDay(Trip $trip, int $position): Day
    {
        $day = new Day();
        $day->setTrip($trip);
        $day->setPosition($position);

        return $day;
    }

    public function testNoOverlap(): void
    {
        $trip = new Trip();

        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 6));
        $d2->setEndDay($this->createDay($trip, 12));

        $this->repositoryMock->expects($this->once())
            ->method('countOverlappingDestinations')
            ->willReturn(0);
        $this->validator->validate($d2, new UniqueDestinationRange());

        $this->assertNoViolation();
    }

    public function testOverlap(): void
    {
        $trip = new Trip();
        $d2 = new Destination($trip);
        $d2->setStartDay($this->createDay($trip, 6));
        $d2->setEndDay($this->createDay($trip, 12));

        $constraint = new UniqueDestinationRange();

        $this->repositoryMock->expects($this->once())
            ->method('countOverlappingDestinations')
            ->willReturn(1);

        $this->validator->validate($d2, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.startDay')
            ->assertRaised();
    }
}
