<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Enum\TripRole;
use App\Form\TripType;
use App\Repository\AccommodationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;
use App\Repository\TripMembershipRepository;
use App\Repository\TripRepository;
use App\Service\TripService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/trip', name: 'app_trip')]
    public function index(TripMembershipRepository $tripMembershipRepository, TripRepository $tripRepository): Response
    {
        //        TODO: Redo query in repo
        $user = $this->getUser();
        $memberships = $tripMembershipRepository->findBy(['member' => $user]);

        return $this->render('trip/index.html.twig', [
            'controller_name' => 'TripController',
            'memberships' => $memberships,
        ]);
    }

    // TODO: create in dialog
    #[Route('/trip/create', name: 'app_trip_create', methods: ['POST', 'GET'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tripMembership = (new TripMembership())
                ->setTrip($trip)
                ->setMember($this->getUser())
                ->setRole(TripRole::ADMIN);

            $entityManager->persist($trip);
            $entityManager->persist($tripMembership);
            $entityManager->flush();

            return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()]);
        }

        return $this->render('trip/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/trip/{id}', name: 'app_trip_show', methods: ['GET'])]
    public function show(
        Trip $trip, TravelItemRepository $travelItemRepository,
        AccommodationRepository $accommodationRepository,
        FlightRepository $flightRepository,
        TripService $tripService,
    ): Response
    {
        $accommodations = $accommodationRepository->findAccommodationsByTrip($trip);

        return $this->render('trip/show.html.twig', [
            'trip'  => $trip,
            'items' => $travelItemRepository->findItemDayPairsForTrip($trip),
            'accommodations' => $accommodations,
            'flights' => $flightRepository->findFlightsByTrip($trip),
            'statistics' => $tripService->getTripStatistics($trip, $accommodations),
        ]);
    }

    #[Route('/trip/{id}/delete', name: 'app_trip_delete', methods: ['POST'])]
    public function delete(Request $request, Trip $trip, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trip->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trip);
            $entityManager->flush();

            $this->addFlash('success', 'Trip deleted successfully.');
        }

        return $this->redirectToRoute('app_trip');
    }
}
