<?php

namespace App\Controller\TravelItem;

use App\Entity\Destination;
use App\Entity\Trip;
use App\Repository\DayRepository;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DestinationController extends AbstractController
{
    #[Route('/travel-item/trip/{trip}/destination/{item}/update-dates', name: 'app_travelitem_destination_update_dates', methods: ['POST'])]
    public function updateDates(
        Request $request,
        EntityManagerInterface $entityManager,
        DayRepository $dayRepository,
        ValidatorInterface $validator,
        DestinationRepository $destinationRepository,
        Trip $trip,
        Destination $item,
    ): Response
    {
        if ($this->isCsrfTokenValid('update' . $item->getId(), $request->request->get('_token'))) {
            $direction = $request->request->get('direction', 'up');
            $newPos = $item->getEndDay()->getPosition() + ('up' === $direction ? 1 : -1);
            $newDay = $dayRepository->findOneBy(['position' => $newPos, 'trip' => $trip]);
            $item->setEndDay($newDay);
            $validator->validate($item);

            if (count($validator->validate($item)) === 0) {
                $entityManager->flush();
            }
        }
        return $this->render('travel_item/destination/show.frame.html.twig', [
            'trip' => $trip,
            'destinations' => $destinationRepository->findDestinationByTrip($trip),
        ]);
    }
}
