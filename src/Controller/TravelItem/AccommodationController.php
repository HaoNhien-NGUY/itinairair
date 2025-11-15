<?php

namespace App\Controller\TravelItem;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Trip;
use App\Form\TravelItem\AccommodationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class AccommodationController extends AbstractController
{
    #[Route('/travel-item/trip/{trip}/accommodation/{item}/{day}', name: 'app_travelitem_accommodation_show', methods: ['GET'])]
    public function show(
        Trip                   $trip,
        Accommodation          $item,
        ?Day                   $day = null,
    ): Response
    {
        return $this->render('travel_item/accommodation/_show.frame.html.twig', [
            'item' => $item,
            'day'  => $day,
            'trip' => $trip,
        ]);
    }

    #[Route('/travel-item/trip/{trip}/accommodation/{item}/edit/{day}', name: 'app_travelitem_accommodation_edit', methods: ['POST', 'GET'])]
    public function edit(
        Request                $request,
        EntityManagerInterface $entityManager,
        Trip                   $trip,
        Accommodation          $item,
        ?Day                   $day = null,
    ): Response
    {
        if ($item->getStartDay()->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $form = $this->createForm(AccommodationType::class, $item, ['action' => $request->getUri(), 'trip' => $trip]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('travel_item/accommodation/_edit.frame.html.twig', [
                'form' => $form,
            ]);
        }

        return $this->render('travel_item/accommodation/_edit.frame.html.twig', [
            'form'       => $form,
            'item'       => $item,
            'trip'       => $trip,
            'day'        => $day,
        ]);
    }
}
