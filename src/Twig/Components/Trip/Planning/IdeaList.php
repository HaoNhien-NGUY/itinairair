<?php

namespace App\Twig\Components\Trip\Planning;

use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Repository\TravelItemRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class IdeaList
{
    public Trip $trip;

    public function __construct(
        private readonly TravelItemRepository $travelItemRepository,
    ) {
    }

    /**
     * @return TravelItem[]
     */
    public function getIdeas(): array
    {
        return $this->travelItemRepository->findItemsForTrip($this->trip, ItemStatus::draft());
    }
}
