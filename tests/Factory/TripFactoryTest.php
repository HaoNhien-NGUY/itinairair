<?php

namespace App\Tests\Factory;

use App\Model\DayView;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use App\Factory\TripFactory;
use App\Entity\Trip;
use App\Entity\Day;
use App\Entity\Destination;
use App\Repository\DestinationRepository;
use App\Repository\TravelItemRepository;
use App\Repository\FlightRepository;
use App\Factory\DayFactory;

class TripFactoryTest extends TestCase
{
    private $destinationRepo;
    private $itemRepo;
    private $flightRepo;
    private $dayFactory;
    private $tripFactory;

    protected function setUp(): void
    {
        $this->itemRepo = $this->createMock(TravelItemRepository::class);
        $this->dayFactory = $this->createMock(DayFactory::class);
        $this->destinationRepo = $this->createMock(DestinationRepository::class);
        $this->flightRepo = $this->createMock(FlightRepository::class);

        $this->itemRepo->method('findItemDayPairsForTrip')->willReturn([]);
        $this->flightRepo->method('countFirstDayOverNightFlight')->willReturn(0);

        $this->tripFactory = new TripFactory(
            $this->itemRepo,
            $this->dayFactory,
            $this->destinationRepo,
            $this->flightRepo,
        );
    }

    #[DataProvider('segmentationProvider')]
    public function testSegmentation(
        int $nbDays,
        array $destinations,
        array $expectedSegments,
    ): void {
        $trip = $this->createMock(Trip::class);
        [$days, $dayViews] = $this->createDays($nbDays);
        $trip->method('getDays')->willReturn($days);

        $groupedDestinations = ['byStartDay' => [], 'byEndDay' => []];

        foreach ($destinations as $id => $range) {
            [$startDay, $endDay] = $range;

            $dest = $this->mockDestination($id);
            $groupedDestinations['byStartDay'][$startDay] = $dest;
            $groupedDestinations['byEndDay'][$endDay] = $dest;
        }

        $this->destinationRepo->method('findDestinationsMappedByDayPosition')
            ->willReturn($groupedDestinations);

        $this->dayFactory->expects($this->exactly($nbDays))
            ->method('createDayView')
            ->willReturnOnConsecutiveCalls(...$dayViews);

        $view = $this->tripFactory->planningView($trip);
        $segments = $view->segments;

        $this->assertCount(count($expectedSegments), $segments);

        foreach ($segments as $i => $segment) {
            [$startDay, $endDay] = $expectedSegments[$i]['dayRange'];

            $this->assertEquals($expectedSegments[$i]['destId'], $segment->destination?->getId());
            $this->assertCount($expectedSegments[$i]['dayCount'], $segment->days);

            foreach (range($startDay, $endDay) as $dayI => $expectedPos) {
                $this->assertEquals($expectedPos, $segment->days[$dayI]?->day->getPosition());
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
            ]
        ];
        yield 'Without gap' => [
            'nbDays' => 4,
            'destinations' => [0 => [1, 2], 1 => [3, 4]],
            'expectedSegments' => [
                ['destId' => 0, 'dayCount' => 2, 'dayRange' => [1, 2]],
                ['destId' => 1, 'dayCount' => 2, 'dayRange' => [3, 4]],
            ]
        ];
        yield 'Overlap' => [
            'nbDays' => 4,
            'destinations' => [0 => [1, 2], 1 => [2, 4]],
            'expectedSegments' => [
                ['destId' => 0, 'dayCount' => 1, 'dayRange' => [1, 1]],
                ['destId' => 1, 'dayCount' => 3, 'dayRange' => [2, 4]],
            ]
        ];
    }

    /**
     * @param int $count
     * @return array{0: ArrayCollection<int, Day>, 1: array<int, DayView>}
     */
    private function createDays(int $count): array
    {
        $days = [];
        $dayViews = [];

        for ($i = 1; $i <= $count; $i++) {
            $day = $this->mockDay($i);
            $days[$i] = $day;
            $dayView = $this->createStub(DayView::class);
            $dayView->day = $day;
            $dayViews[] = $dayView;
        }
        return [new ArrayCollection($days), $dayViews];
    }

    private function mockDay(int $position): Day
    {
        $day = $this->createMock(Day::class);
        $day->method('getPosition')->willReturn($position);
        $day->method('getId')->willReturn($position);

        return $day;
    }

    private function mockDestination(int $id): Destination
    {
        $dest = $this->createMock(Destination::class);
        $dest->method('getId')->willReturn($id);
        return $dest;
    }
}
