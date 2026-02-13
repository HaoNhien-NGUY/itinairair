<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\TripType;
use App\Repository\AccommodationRepository;
use App\Repository\FlightRepository;
use App\Repository\TravelItemRepository;
use App\Repository\TripMembershipRepository;
use App\Service\TripService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/trip')]
final class TripController extends AbstractController
{
    #[Route('/', name: 'app_trip')]
    public function index(): Response
    {
        return $this->render('trip/index.html.twig');
    }

    #[Route('/create', name: 'app_trip_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
        TripService $tripService,
        #[CurrentUser] User $user,
    ): Response {
        $trip = Trip::create($user);
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tripService->create($trip);

            return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()]);
        }

        return $this->render('trip/create.html.twig', [
            'form' => $form,
            'trip' => $trip,
        ]);
    }

    #[Route('/edit/{trip}', name: 'app_trip_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, Trip $trip): Response
    {
        $form = $this->createForm(TripType::class, $trip, ['action' => $request->getUri()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()]);
        }

        return $this->render('trip/create.html.twig', [
            'form' => $form,
            'trip' => $trip,
        ]);
    }

    #[IsGranted('TRIP_VIEW', 'trip')]
    #[Route('/{id}', name: 'app_trip_show', methods: ['GET'])]
    public function show(Trip $trip): Response
    {
        return $this->render('trip/show.html.twig', [
            'trip'  => $trip,
        ]);
    }

    #[IsGranted('TRIP_VIEW', 'trip')]
    #[Route('/{id}/itinerary', name: 'app_trip_itinerary', methods: ['GET'])]
    public function itinerary(
        Trip $trip,
    ): Response {
        return $this->render('trip/itinerary.html.twig', [
            'trip'  => $trip,
        ]);
    }

    #[IsGranted('TRIP_VIEW', 'trip')]
    #[Route('/{id}/bookings', name: 'app_trip_bookings', methods: ['GET'])]
    public function bookings(
        Trip $trip,
        AccommodationRepository $accommodationRepository,
        FlightRepository $flightRepository,
    ): Response {
        return $this->render('trip/bookings.html.twig', [
            'trip'  => $trip,
            'accommodations' => $accommodationRepository->findAccommodationsByTrip($trip),
            'flights' => $flightRepository->findFlightsByTrip($trip),
        ]);
    }

    #[IsGranted('TRIP_EDIT', 'trip')]
    #[Route('/{trip}/share-link', name: 'app_trip_share_link', methods: ['GET', 'POST'])]
    public function shareLink(
        Trip $trip,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($request->isMethod('POST')
            && !$trip->getInviteToken()
            && $this->isCsrfTokenValid('toggle_share_link', $request->request->get('_token'))) {
            $trip->generateInviteToken();
            $entityManager->flush();

            return $this->render('trip/share/_share_link_modal.frame.html.twig', [
                'success' => true,
                'trip'    => $trip,
            ]);
        }

        return $this->render('trip/share/_share_link_modal.frame.html.twig', ['trip' => $trip]);
    }

    #[Route('/join/{inviteToken:trip}', name: 'app_trip_join')]
    public function join(
        ?Trip $trip,
        EntityManagerInterface $entityManager,
        TripMembershipRepository $tripMembershipRepository,
        #[CurrentUser] User $user,
    ): Response {
        if (!$trip) {
            // TODO: render page to suggest inviteToken has expired
            throw $this->createNotFoundException('This invite link is invalid or has expired.');
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $membership = $tripMembershipRepository->findOneBy(['trip' => $trip, 'member' => $user]);

        if (!$membership) {
            $membership = $trip->addMember($user);
            $entityManager->persist($membership);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()]);
    }

    #[IsGranted('TRIP_MANAGE', 'trip')]
    #[Route('/{id}/delete', name: 'app_trip_delete', methods: ['POST'])]
    public function delete(Request $request, Trip $trip, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trip->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trip);
            $entityManager->flush();

            $this->addFlash('success', 'Trip deleted successfully.');
        }

        return $this->redirectToRoute('app_trip');
    }
}
