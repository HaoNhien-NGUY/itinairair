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
    public function insertItineraryItem(TravelItem $item, int $position, Day $day = null): void
    {
        $this->entityManager->wrapInTransaction(function () use ($day, $item, $position) {
            if ($item->getItemType()->isPositionable()) {
                $item->setPosition($position);
                $this->travelItemRepository->makeSpaceAtPosition($day, $position);
            }

            if ($day) $item->setStartDay($day);

            $this->entityManager->persist($item);

            $this->entityManager->flush();
        });
    }
}
