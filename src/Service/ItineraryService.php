<?php

namespace App\Service;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Repository\TravelItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ItineraryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TravelItemRepository   $travelItemRepository,
    ) {
    }

    /**
     * @throws Exception
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
     * @param array<Day> $days
     * @return array{days: array<Day>, itemsByDayId: array}
     */
    public function daysWithItems(array $days): array
    {
        $itemsByDayId = [];
        foreach ($days as $day) $itemsByDayId[$day->getId()] = $this->travelItemRepository->findItemsForDay($day);

        return [
            'days' => $days,
            'itemsByDayId' => $itemsByDayId,
        ];
    }
}
