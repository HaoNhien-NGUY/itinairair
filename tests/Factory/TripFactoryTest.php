<?php

namespace App\Tests\Factory;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use App\Factory\DayFactory;
use App\Factory\TripFactory;
use App\Model\DayView;
use App\Repository\DestinationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TripFactoryTest extends TestCase
{
    private DestinationRepository&MockObject $destinationRepo;
    private FlightRepository&MockObject $flightRepo;
    private DayFactory&MockObject $dayFactory;
    private TripFactory $tripFactory;

    protected function setUp(): void
    {
        $itemRepo = $this->createMock(TravelItemRepository::class);
        $this->dayFactory = $this->createMock(DayFactory::class);
        $this->destinationRepo = $this->createMock(DestinationRepository::class);
        $this->flightRepo = $this->createMock(FlightRepository::class);

        $itemRepo->method('findItemDayPairsForTrip')->willReturn([]);

        $this->tripFactory = new TripFactory(
            $itemRepo,
            $this->dayFactory,
            $this->destinationRepo,
            $this->flightRepo,
        );
    }

    /**
     * @param array<int, array{int, int}>                                             $destinations
     * @param list<array{destId: int|null, dayCount: int, dayRange: array{int, int}}> $expectedSegments
     *
     * @throws Exception
     */
    #[DataProvider('segmentationProvider')]
    public function testSegmentation(
        int $nbDays,
        array $destinations,
        array $expectedSegments,
    ): void {
        $trip = $this->createMock(Trip::class);
        [$days, $dayViews] = $this->createDays($nbDays);

        $trip->method('getDays')->willReturn($days);
        $this->flightRepo->method('countFirstDayOverNightFlight')->willReturn(1);
        $this->destinationRepo->method('findDestinationsMappedByDayPosition')
            ->willReturn($this->createGroupedDestinations($destinations));

        $this->dayFactory->expects($this->exactly($nbDays))
            ->method('createDayView')
            ->willReturnOnConsecutiveCalls(...$dayViews);

        $view = $this->tripFactory->planningView($trip);
        $segments = $view->segments;

        $this->assertSame($trip, $view->trip);
        $this->assertCount(count($expectedSegments), $segments);
        $this->assertTrue($view->startWithTravel);

        foreach ($segments as $i => $segment) {
            [$startDay, $endDay] = $expectedSegments[$i]['dayRange'];

            $this->assertEquals($expectedSegments[$i]['destId'], $segment->destination?->getId());
            $this->assertCount($expectedSegments[$i]['dayCount'], $segment->days);

            foreach (range($startDay, $endDay) as $dayI => $expectedPos) {
                $this->assertEquals($expectedPos, ($segment->days[$dayI] ?? null)?->day->getPosition());
            }
        }
    }

    public static function segmentationProvider(): \Generator
    {
        yield 'With gap' => [
            'nbDays' => 5,
            'destinations' => [0 => [1, 2], 1 => [4, 5]], // [id => [startDay, endDay], ...]
            'expectedSegments' => [
                ['destId' => 0, 'dayCount' => 2, 'dayRange' => [1, 2]],
                ['destId' => null, 'dayCount' => 1, 'dayRange' => [3, 3]],
                ['destId' => 1, 'dayCount' => 2, 'dayRange' => [4, 5]],
            ],
        ];
        yield 'Without gap' => [
            'nbDays' => 4,
            'destinations' => [0 => [1, 2], 1 => [3, 4]],
            'expectedSegments' => [
                ['destId' => 0, 'dayCount' => 2, 'dayRange' => [1, 2]],
                ['destId' => 1, 'dayCount' => 2, 'dayRange' => [3, 4]],
            ],
        ];
        yield 'Overlap' => [
            'nbDays' => 4,
            'destinations' => [0 => [1, 2], 1 => [2, 4]],
            'expectedSegments' => [
                ['destId' => 0, 'dayCount' => 1, 'dayRange' => [1, 1]],
                ['destId' => 1, 'dayCount' => 3, 'dayRange' => [2, 4]],
            ],
        ];
        yield 'Empty trip' => [
            'nbDays' => 0,
            'destinations' => [],
            'expectedSegments' => [],
        ];
        yield 'Days but no destinations' => [
            'nbDays' => 3,
            'destinations' => [],
            'expectedSegments' => [
                ['destId' => null, 'dayCount' => 3, 'dayRange' => [1, 3]],
            ],
        ];
        yield 'Gap at the start and end' => [
            'nbDays' => 6,
            'destinations' => [0 => [2, 4]],
            'expectedSegments' => [
                ['destId' => null, 'dayCount' => 1, 'dayRange' => [1, 1]],
                ['destId' => 0, 'dayCount' => 3, 'dayRange' => [2, 4]],
                ['destId' => null, 'dayCount' => 2, 'dayRange' => [5, 6]],
            ],
        ];
    }

    /**
     * @return array{ArrayCollection<int, Day>, array<int, DayView>}
     */
    private function createDays(int $count): array
    {
        $days = [];
        $dayViews = [];

        for ($i = 1; $i <= $count; ++$i) {
            $day = $this->stubDay($i);
            $days[$i] = $day;
            $dayView = $this->createStub(DayView::class);
            $dayView->day = $day;
            $dayViews[] = $dayView;
        }

        return [new ArrayCollection($days), $dayViews];
    }

    private function stubDay(int $position): Day
    {
        $day = $this->createStub(Day::class);
        $day->method('getPosition')->willReturn($position);
        $day->method('getId')->willReturn($position);

        return $day;
    }

    private function stubDestination(int $id): Destination
    {
        $dest = $this->createStub(Destination::class);
        $dest->method('getId')->willReturn($id);

        return $dest;
    }

    /**
     * @param array<int, array{int, int}> $destinations
     *
     * @return array{byStartDay: array<int, Destination>, byEndDay: array<int, Destination>}
     */
    private function createGroupedDestinations(array $destinations): array
    {
        $groupedDestinations = ['byStartDay' => [], 'byEndDay' => []];

        foreach ($destinations as $id => $range) {
            [$startDay, $endDay] = $range;

            $dest = $this->stubDestination($id);
            $groupedDestinations['byStartDay'][$startDay] = $dest;
            $groupedDestinations['byEndDay'][$endDay] = $dest;
        }

        return $groupedDestinations;
    }
}
