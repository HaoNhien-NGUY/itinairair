<?php

namespace App\Controller\TravelItem;

use App\Entity\TravelItem;
use App\Entity\Trip;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ItemIdeaController extends AbstractController
{
    #[Route('/travel-item/trip/{trip}/idea/{item}', name: 'app_travelitem_idea_show', methods: ['GET'])]
    public function show(
        Trip $trip,
        TravelItem $item,
    ): Response {
        return $this->render('travel_item/idea/_base_show.frame.html.twig', [
            'item' => $item,
            'trip' => $trip,
        ]);
    }

    #[Route('/travel-item/trip/{trip}/idea/{item}/details', name: 'app_travelitem_idea_details', methods: ['GET'])]
    public function details(
        Trip $trip,
        TravelItem $item,
    ): Response {
        return $this->render('travel_item/idea/_details.frame.html.twig', [
            'item' => $item,
            'trip' => $trip,
        ]);
    }
}
