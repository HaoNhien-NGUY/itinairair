<?php

namespace App\Twig\Components\Trip\Itinerary;

use App\Entity\Trip;
use App\Model\Trip\ItineraryViewModel;
use App\Presenter\ItineraryPresenter;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Timeline
{
    public Trip $trip;

    public function __construct(
        private readonly ItineraryPresenter $itineraryPresenter,
    ) {
    }

    public function getItinerary(): ItineraryViewModel
    {
        return $this->itineraryPresenter->createItineraryViewModel($this->trip);
    }
}
