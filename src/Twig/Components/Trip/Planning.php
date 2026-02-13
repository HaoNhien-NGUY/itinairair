<?php

namespace App\Twig\Components\Trip;

use App\Entity\Trip;
use App\Model\Trip\PlanningViewModel;
use App\Presenter\TripPresenter;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Planning
{
    public Trip $trip;

    private ?PlanningViewModel $planning = null;

    public function __construct(
        private readonly TripPresenter $tripPresenter,
    ) {
    }

    public function getPlanning(): PlanningViewModel
    {
        if (null === $this->planning) {
            $this->planning = $this->tripPresenter->createPlanningViewModel($this->trip);
        }

        return $this->planning;
    }
}
