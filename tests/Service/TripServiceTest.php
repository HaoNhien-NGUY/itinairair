<?php

namespace App\Tests\Service;

use App\Entity\Destination;
use App\Entity\Trip;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Service\TripService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TripServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private DestinationRepository&MockObject $destinationRepository;
    private FlightRepository&MockObject $flightRepository;
    private TripService $tripService;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private int $expectedDays;

    protected function setUp(): void
    {
        $this->destinationRepository = $this->createMock(DestinationRepository::class);
        $this->flightRepository = $this->createMock(FlightRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->startDate = new \DateTime('2024-01-01');
        $this->endDate = new \DateTime('2024-01-05');
        $this->expectedDays = $this->startDate->diff($this->endDate)->days + 1;

        $this->tripService = new TripService(
            $this->destinationRepository,
            $this->flightRepository,
            $this->entityManager,
        );
    }

    public function testCreatePersistsAndFlushes(): void
    {
        $trip = new Trip();

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($trip);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->tripService->create($trip);

        $this->assertSame($trip, $result);
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

        $this->assertCount($this->expectedDays + $expectedDaysModifier, $days);
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

    /**
     * @param string[] $countries
     * @param string[] $cities
     */
    #[DataProvider('cityAndCountryProvider')]
    public function testGetTripStatisticsCalculatesCorrectly(array $countries, array $cities): void
    {
        $trip = new Trip();

        $trip->setStartDate($this->startDate);
        $trip->setEndDate($this->endDate);
        $tripDurationInDays = $trip->getDurationInDays();
        $this->tripService->addOrRemoveTripDays($trip, $tripDurationInDays);

        $this->destinationRepository->method('findDestinationCountriesByTrip')
            ->willReturn($countries);
        $this->destinationRepository->method('findDestinationCitiesByTrip')
            ->willReturn($cities);

        $stats = $this->tripService->getTripStatistics($trip);

        $this->assertEquals($tripDurationInDays, $stats['duration']);
        $this->assertEquals(count($countries), $stats['country_count']);
        $this->assertEquals(count($cities), $stats['city_count']);
        $this->assertEquals($countries, $stats['countries']);
        $this->assertEquals($cities, $stats['cities']);
    }

    public static function cityAndCountryProvider(): \Generator
    {
        yield '2 countries and 3 cities' => [
            'countries' => ['France', 'Espagne'],
            'cities' => ['Paris', 'Madrid', 'Barcelone'],
        ];
        yield '1 country and 3 cities' => [
            'countries' => ['France'],
            'cities' => ['Paris', 'Lyon', 'Toulon'],
        ];
        yield '0 country and 0 cities' => [
            'countries' => [],
            'cities' => [],
        ];
    }
}
