<?php

namespace App\Controller\TravelItem;

use App\Entity\TravelItem;
use App\Entity\Trip;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActivityController extends AbstractController
{
    #[Route('/travel-item/trip/{trip}/activity/{item}', name: 'app_travelitem_activity_show', methods: ['GET'])]
    public function show(
        Trip $trip,
        TravelItem $item,
    ): Response {
        return $this->render('travel_item/activity/_base_show.frame.html.twig', [
            'item' => $item,
            'trip' => $trip,
        ]);
    }

    #[Route('/travel-item/trip/{trip}/activity/{item}/details', name: 'app_travelitem_activity_details', methods: ['GET'])]
    public function details(
        Trip $trip,
        TravelItem $item,
    ): Response {
        return $this->render('travel_item/activity/_details.frame.html.twig', [
            'item' => $item,
            'trip' => $trip,
        ]);
    }
}
