<?php

namespace App\Twig\Components;

use App\Entity\Day;
use App\Entity\Trip;
use App\Repository\DayRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PlanningDashboard
{
    public Trip $trip;

    public function __construct(private readonly DayRepository $dayRepository)
    {
    }

    public function getCurrentDay(): ?Day
    {
        return $this->dayRepository->findCurrentDayForTrip($this->trip);
    }
}
