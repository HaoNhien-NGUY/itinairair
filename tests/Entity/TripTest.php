<?php

namespace App\Tests\Entity;

use App\Entity\Trip;
use DateTime;
use App\Exception\InvalidTripDatesException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TripTest extends TestCase
{
    #[DataProvider('durationInDaysData')]
    public function testGetDurationInDays(DateTime $startDate, DateTime $endDate, int $expected)
    {
        $trip = new Trip();

        $trip->setStartDate($startDate);
        $trip->setEndDate($endDate);

        $duration = $trip->getDurationInDays();

        $this->assertEquals($expected, $duration);
    }

    public static function durationInDaysData(): iterable
    {
        yield '5 days' => [new DateTime('2024-01-01'), new DateTime('2024-01-05'), 5];
        yield '2 days' => [new DateTime('2024-01-01'), new DateTime('2024-01-02'), 2];
        yield '60 days' => [new DateTime('2024-01-01'), new DateTime('2024-02-29'), 60];
    }

    #[DataProvider('invalidDaysData')]
    public function testDurationThrowsException(?DateTime $startDate, ?DateTime $endDate)
    {
        $trip = new Trip();

        $trip->setStartDate($startDate);
        $trip->setEndDate($endDate);

        $this->expectException(InvalidTripDatesException::class);

        $trip->getDurationInDays();
    }


    public static function invalidDaysData(): iterable
    {
        yield 'EndDate missing' => [new DateTime('2024-01-01'), null];
        yield 'StartDate missing' => [null, new DateTime('2024-01-02')];
        yield 'Start and End date missing' => [null, null];
        yield 'End before Start date' => [new DateTime('2024-01-05'), new DateTime('2024-01-01')];
    }
}
