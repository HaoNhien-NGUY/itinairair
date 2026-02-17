<?php

namespace App\Tests\Presenter;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\Note;
use App\Entity\Trip;
use App\Presenter\DayPresenter;
use App\Repository\TravelItemRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

class DayPresenterTest extends TestCase
{
    private TravelItemRepository&MockObject $travelItemRepo;
    private MockClock $clock;
    private \DateTime $referenceDate;
    private DayPresenter $dayPresenter;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $this->referenceDate = new \DateTime('2025-02-20 10:00:00');
        $this->travelItemRepo = $this->createMock(TravelItemRepository::class);
        $this->clock = new MockClock(\DateTimeImmutable::createFromMutable($this->referenceDate));

        $this->dayPresenter = new DayPresenter($this->travelItemRepo, $this->clock);

    }

    public function testItemsAreCategorizedCorrectly(): void
    {
        $trip = new Trip();
        $currentDay = (new Day())
            ->setTrip($trip)
            ->setdate($this->referenceDate)
            ->setPosition(1);
        $otherDay = (new Day())
            ->setTrip($trip)
            ->setDate($this->referenceDate->modify('+1 day'))
            ->setPosition(2);

        $accommodation = new Accommodation($trip, $currentDay);
        $destination = new Destination($trip, $currentDay);
        $flightSameDay = new Flight($trip, $currentDay);
        $flightStart = (new Flight($trip, $currentDay))->setEndDay($otherDay);
        $flightEnd = (new Flight($trip, $otherDay))->setEndDay($currentDay);

        $items = [$accommodation, $destination, $flightSameDay, $flightStart, $flightEnd];

        $viewModel = $this->dayPresenter->createDayViewModel($currentDay, $items);

        // 4. Assert
        $this->assertCount(1, $viewModel->accommodations);
        $this->assertCount(1, $viewModel->destinations);

        // Check Flight Buckets
        $this->assertCount(1, $viewModel->flightSameDay);
        $this->assertSame($flightSameDay, $viewModel->flightSameDay[0]);

        $this->assertCount(1, $viewModel->flightStartDay);
        $this->assertSame($flightStart, $viewModel->flightStartDay[0]);

        $this->assertCount(1, $viewModel->flightEndDay);
        $this->assertSame($flightEnd, $viewModel->flightEndDay[0]);
    }

    public function testRepositoryIsCalledWhenItemsAreNull(): void
    {
        $trip = new Trip();
        $day = new Day();
        $day->setDate($this->referenceDate);
        $item = new Note($trip, $day);

        $this->travelItemRepo->expects($this->once())
            ->method('findItemsForDay')
            ->with($day)
            ->willReturn([$item]);

        $dayModel = $this->dayPresenter->createDayViewModel($day);

        $this->assertCount(1, $dayModel->positionable);
    }

    /**
     * @throws \Exception
     */
    #[DataProvider('isTodayProvider')]
    public function testIsTodayFlagWorks(string $clockDate, string $dayDate, bool $expected): void
    {
        $this->clock->modify($clockDate);

        $day = new Day();
        $day->setDate(new \DateTime($dayDate));
        $dayModel = $this->dayPresenter->createDayViewModel($day, []);
        $this->assertSame($expected, $dayModel->isToday);
    }

    public static function isTodayProvider(): \Generator
    {
        yield 'True same day' => ['clockDate' => '2025-02-20 10:00:00', 'dayDate' => '2025-02-20', 'expected' => true];
        yield 'day past 2 days ago' => ['clockDate' => '2025-02-22 10:00:00', 'dayDate' => '2025-02-20', 'expected' => false];
        yield 'in 2 days from now' => ['clockDate' => '2025-02-18 10:00:00', 'dayDate' => '2025-02-20', 'expected' => false];
    }

    public function testIsTripStartFlag(): void
    {
        $trip = new Trip();
        $startDay = (new Day())
            ->setTrip($trip)
            ->setDate($this->referenceDate)
            ->setPosition(1);
        $endDay = (new Day())
            ->setTrip($trip)
            ->setDate($this->referenceDate->modify('+1 day'))
            ->setPosition(2);

        $flight = (new Flight($trip, $startDay))
            ->setEndDay($endDay);

        $dayModel = $this->dayPresenter->createDayViewModel($startDay, [$flight]);

        $this->assertTrue($dayModel->isTripStart);
    }
}
