<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Activity;
use App\Entity\Day;
use App\Entity\Trip;
use App\Service\ItineraryService;
use App\Tests\FunctionalTestCase;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Container;

class ItineraryServiceTest extends FunctionalTestCase
{
    private Container $container;
    private ObjectManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param array<int, string> $existing
     * @param array<int, string> $expectedOrder
     */
    #[DataProvider('insetScenarioProvider')]
    public function testInsertTravelItemShiftsPositions(array $existing, int $insertAt, array $expectedOrder): void
    {
        $service = $this->container->get(ItineraryService::class);

        $user = $this->getUser();
        $trip = $this->createTrip($user);

        $day = (new Day())->setTrip($trip)->setPosition(1)->setDate($trip->getStartDate());
        $this->em->persist($day);

        foreach ($existing as $position => $name) {
            $this->createItem($trip, $day, $position, $name);
        }

        $this->em->flush();

        $newItem = (new Activity($trip, $day))->setName('C');

        $service->insertTravelItem($newItem, $day, $insertAt);

        $this->em->clear();

        /** @var Activity[] $allItems */
        $allItems = $this->em->getRepository(Activity::class)->findBy(['startDay' => $day], ['position' => 'ASC']);
        $this->assertCount(count($expectedOrder), $allItems);

        foreach ($allItems as $i => $item) {
            $this->assertEquals($expectedOrder[$i], $item->getName());
        }
    }

    public static function insetScenarioProvider(): \Generator
    {
        yield 'insert item at start' => [
            'existing' => [1 => 'A', 2 => 'B'],
            'insertAt' => 1,
            'expectedOrder' => ['C', 'A', 'B'],
        ];
        yield 'insert item at end' => [
            'existing' => [1 => 'A', 2 => 'B'],
            'insertAt' => 2,
            'expectedOrder' => ['A', 'C', 'B'],
        ];
        yield 'insert item at end without shift' => [
            'existing' => [1 => 'A'],
            'insertAt' => 2,
            'expectedOrder' => ['A', 'C'],
        ];
        yield 'insert item in empty day' => [
            'existing' => [],
            'insertAt' => 1,
            'expectedOrder' => ['C'],
        ];
    }

    private function createItem(Trip $trip, Day $day, int $position, string $name): void
    {
        $item = (new Activity($trip, $day))
            ->setPosition($position)
            ->setName($name);

        $this->em->persist($item);
    }
}
