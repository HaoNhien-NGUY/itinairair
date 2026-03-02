<?php

namespace App\Service;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Repository\TravelItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

readonly class ItineraryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TravelItemRepository $travelItemRepository,
        private WorkflowInterface $travelItemStatusStateMachine,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function insertTravelItem(TravelItem $item, Day $day, ?int $position = null): void
    {
        $this->entityManager->wrapInTransaction(function () use ($item, $day, $position) {
            if ($item->getItemType()->isPositionable()) {
                $position = $position ?? 0;
                $item->setPosition($position);
                $this->travelItemRepository->makeSpaceAtPosition($day, $position);
            }

            $this->entityManager->persist($item);
            $this->entityManager->flush();
        });
    }

    /**
     * @param array<int, string> $orderedItems
     *
     * @return Day[]
     */
    public function reorderDayItems(Day $day, array $orderedItems): array
    {
        $daysToUpdate = [$day];

        $items = $this->travelItemRepository->findBy(
            ['id' => $orderedItems, 'startDay' => $day, 'status' => ItemStatus::committed()],
        );
        $itemsById = [];

        foreach ($items as $item) {
            $itemsById[$item->getId()] = $item;
        }

        foreach ($orderedItems as $position => $id) {
            if (isset($itemsById[$id])) {
                $itemsById[$id]->setPosition($position);

                continue;
            }

            $newItem = $this->travelItemRepository->findOneBy(['id' => $id, 'trip' => $day->getTrip()]);

            if (!$newItem) {
                continue;
            }

            if ($dayToUpdate = $newItem->getStartDay()) {
                $daysToUpdate[] = $dayToUpdate;
            }

            $newItem
                ->setPosition($position)
                ->setStartDay($day);

            if (in_array($newItem->getStatus(), ItemStatus::draft()) && $this->travelItemStatusStateMachine->can($newItem, 'plan')) {
                $this->travelItemStatusStateMachine->apply($newItem, 'plan');
            }
        }

        return $daysToUpdate;
    }

    /**
     * @return Day[]
     */
    public function itemToIdea(Trip $trip, ?int $itemId = null): array
    {
        $item = $this->travelItemRepository->findOneBy(['id' => $itemId, 'trip' => $trip, 'status' => ItemStatus::committed()]);

        if (!$item || !$this->travelItemStatusStateMachine->can($item, 'to_idea')) {
            return [];
        }

        $daysToUpdate[] = $item->getStartDay();

        $this->travelItemStatusStateMachine->apply($item, 'to_idea');

        $item->setPosition(null);

        return $daysToUpdate;
    }
}
