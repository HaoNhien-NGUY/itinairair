<?php

namespace App\Twig\Components\Trip\Planning;

use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Factory\DayFactory;
use App\Model\DayView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Day
{
    public Trip $trip;

    public DayView $dayView;

    public \App\Entity\Day $day;

    public ?TravelItem $newItem = null;

    public function __construct(private readonly DayFactory $dayFactory)
    {
    }

    public function mount(\App\Entity\Day $day, ?DayView $dayView = null): void
    {
        if (!$dayView) {
            $this->dayView = $this->dayFactory->dayView($day);
        }
    }
}
