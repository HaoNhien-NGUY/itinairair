<?php

namespace App\Service;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Enum\ItemStatus;
use App\Repository\TravelItemRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ItineraryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TravelItemRepository $travelItemRepository,
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

            $newItem->setPosition($position)->setStartDay($day);

            if (in_array($newItem->getStatus(), ItemStatus::draft())) {
                $newItem->setStatus(ItemStatus::PLANNED);
            }
        }

        return $daysToUpdate;
    }
}
