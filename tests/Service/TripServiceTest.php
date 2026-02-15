<?php

namespace App\Tests\Service;

use App\Entity\Trip;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Service\TripService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TripServiceTest extends TestCase
{
    private TripService $tripService;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private int $expectedDays;

    protected function setUp(): void
    {
        $destinationRepository = $this->createMock(DestinationRepository::class);
        $flightRepository = $this->createMock(FlightRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->startDate = new \DateTime('2024-01-01');
        $this->endDate = new \DateTime('2024-01-05');
        $this->expectedDays = $this->startDate->diff($this->endDate)->days + 1;

        $this->tripService = new TripService(
            $destinationRepository,
            $flightRepository,
            $entityManager
        );
    }

    #[DataProvider('datesProvider')]
    public function testAddOrRemoveTripDays(?string $startDateModifier, ?string $endDateModifier, int $expectedDaysModifier): void
    {
        $trip = new Trip();

        $trip->setStartDate($this->startDate);
        $trip->setEndDate($this->endDate);

        if ($startDateModifier) {
            $trip->setStartDate((clone $this->startDate)->modify($startDateModifier));
        }

        if ($endDateModifier) {
            $trip->setEndDate((clone $this->endDate)->modify($endDateModifier));
        }

        $this->tripService->addOrRemoveTripDays($trip, $trip->getDurationInDays());

        $days = $trip->getDays();

        $this->assertCount($this->expectedDays + $expectedDaysModifier, $trip->getDays());
    }

    public static function datesProvider(): \Generator
    {
        yield 'push end day by 5 days' => [
            'startDateModifier' => null,
            'endDateModifier' => '+5 days',
            'expectedDaysModifier' => +5,
        ];
        yield 'advance end day by 2 days' => [
            'startDateModifier' => null,
            'endDateModifier' => '-2 days',
            'expectedDaysModifier' => -2,
        ];
        yield 'advance start day by 2 days' => [
            'startDateModifier' => '-2 days',
            'endDateModifier' => null,
            'expectedDaysModifier' => +2,
        ];
        yield 'advance start day by 2 days and push end day by 5 days' => [
            'startDateModifier' => '-2 days',
            'endDateModifier' => '+5 days',
            'expectedDaysModifier' => +7,
        ];
    }
}
