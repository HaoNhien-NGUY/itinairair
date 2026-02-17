<?php

namespace App\Tests\Presenter;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use App\Presenter\ItineraryPresenter;
use App\Service\TripService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItineraryPresenterTest extends TestCase
{
    private TripService&MockObject $tripService;
    private ItineraryPresenter $itineraryPresenter;

    protected function setUp(): void
    {
        $this->tripService = $this->createMock(TripService::class);
        $this->itineraryPresenter = new ItineraryPresenter($this->tripService);
    }

    /**
     * @param array<int, array{int, int}> $destinationsDays
     * @param array<int, array{int, int}> $expectedGaps
     */
    #[DataProvider('segmentationProvider')]
    public function testSegmentation(
        int $nbDays,
        array $destinationsDays,
        array $expectedGaps,
    ): void {
        $trip = $this->createMock(Trip::class);
        $days = $this->createDays($nbDays);
        $destinations = $this->createDestinations($destinationsDays, $days);
        $trip->method('getFirstDay')->willReturn($days[array_key_first($days)]);
        $trip->method('getLastDay')->willReturn($days[array_key_last($days)]);

        $this->tripService->expects($this->once())
            ->method('getTripItinerary')
            ->with($trip)
            ->willReturn($destinations);

        $view = $this->itineraryPresenter->createItineraryViewModel($trip);
        $segments = $view->segments;

        $this->assertEquals($destinations[0]->getStartDay()->getPosition() - 1, $view->initialGap);
        $this->assertCount(count($destinations), $segments);

        foreach ($destinationsDays as $i => [$startDayPos, $endDayPos]) {
            $nbNights = $endDayPos - $startDayPos;
            $this->assertEquals($nbNights, $segments[$i]->nights);
            $this->assertEquals($expectedGaps[$i], $segments[$i]->gapNextDay);
        }

    }

    public static function segmentationProvider(): \Generator
    {
        yield '2 destinations fill the whole trip with no gap' => [
            'nbDays' => 5,
            'destinationsDays' => [[1, 3], [3, 5]], // [[startDayPos, endDayPos], ...]
            'expectedGaps' => [0, 0],
        ];
        yield '1 destinations with 2 days initial gap' => [
            'nbDays' => 5,
            'destinationsDays' => [[2, 3]],
            'expectedGaps' => [2],
        ];
        yield '2 destinations with 2 days gap between' => [
            'nbDays' => 5,
            'destinationsDays' => [[1, 2], [3, 5]],
            'expectedGaps' => [1, 0],
        ];
        yield '2 destinations with 2 days gap between and 1 day gap at the end' => [
            'nbDays' => 5,
            'destinationsDays' => [[1, 2], [3, 4]],
            'expectedGaps' => [1, 1],
        ];
    }

    public function testEmptyTripCalculatesInitialGapBasedOnLastDay(): void
    {
        $day1 = $this->stubDay(1);
        $day5 = $this->stubDay(5);

        $trip = $this->createMock(Trip::class);
        $trip->method('getFirstDay')->willReturn($day1);
        $trip->method('getLastDay')->willReturn($day5);

        $this->tripService->method('getTripItinerary')->willReturn([]);

        $viewModel = $this->itineraryPresenter->createItineraryViewModel($trip);

        $this->assertEquals(4, $viewModel->initialGap);
        $this->assertEmpty($viewModel->segments);
    }

    /**
     * @return array<int, Day>
     */
    private function createDays(int $nbDays): array
    {
        $days = [];

        for ($dayPos = 1; $dayPos <= $nbDays; ++$dayPos) {
            $days[$dayPos] = $this->stubDay($dayPos);
        }

        return $days;
    }

    private function stubDay(int $position): Day
    {
        $day = $this->createStub(Day::class);
        $day->method('getPosition')->willReturn($position);
        $day->method('getId')->willReturn($position);

        return $day;
    }

    /**
     * @param array<int, array{int, int}> $destinations
     * @param array<int, Day>             $days
     *
     * @return array<int, Destination>
     */
    private function createDestinations(array $destinations, array $days): array
    {
        $output = [];

        foreach ($destinations as [$startDayPos, $endDatePos]) {
            $output[] = $this->stubDestination($days[$startDayPos], $days[$endDatePos]);
        }

        return $output;
    }

    private function stubDestination(Day $start, Day $end): Destination
    {
        $dest = $this->createStub(Destination::class);
        $dest->method('getStartDay')->willReturn($start);
        $dest->method('getEndDay')->willReturn($end);
        $dest->method('getDurationInDays')->willReturn($end->getPosition() - $start->getPosition());

        return $dest;
    }
}
