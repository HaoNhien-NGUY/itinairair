<?php

namespace App\Twig\Components\Trip\Planning;

use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Model\Trip\DayViewModel;
use App\Presenter\DayPresenter;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Day
{
    public Trip $trip;

    public DayViewModel $dayView;

    public \App\Entity\Day $day;

    public ?TravelItem $newItem = null;

    public function __construct(private readonly DayPresenter $dayFactory)
    {
    }

    public function mount(\App\Entity\Day $day, ?DayViewModel $dayView = null): void
    {
        if (!$dayView) {
            $this->dayView = $this->dayFactory->createDayViewModel($day);
        }
    }
}
