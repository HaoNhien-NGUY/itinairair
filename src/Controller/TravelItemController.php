<?php

namespace App\Controller;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
use App\Enum\TripRole;
use App\Form\TripType;
use App\Repository\DayRepository;
use App\Repository\TravelItemRepository;
use App\Service\ItineraryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class TravelItemController extends AbstractController
{
    #[Route('/travel-item', name: 'app_travel_item')]
    public function index(): Response
    {
        return $this->render('travel_item/index.html.twig', [
            'controller_name' => 'TravelItemController',
        ]);
    }


    #[Route('/travel-item/{item}/delete', name: 'app_travelitem_delete', methods: ['POST'])]
    public function delete(Request $request, TravelItem $item, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();

            $this->addFlash('success', 'Item deleted successfully.');
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/travel-item/trip/{trip}/day/{day}', name: 'app_travelitem_create_day_item', methods: ['POST', 'GET'])]
    public function createDayItem(
        Request $request,
        TravelItemRepository $travelItemRepository,
        ItineraryService $itineraryService,
        Trip $trip,
        Day $day,
        #[MapQueryParameter] TravelItemType $type = TravelItemType::ACTIVITY,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
        #[MapQueryParameter] ?int $position = 0,
    ): Response
    {
        if ($day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance()->setStatus($status);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itineraryService->insertItineraryItem($item, $position, $day);

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->renderBlock('travel_item/create/day_item.html.twig', 'success_stream', [
                'items'      => $travelItemRepository->findItemsForDay($day),
                'trip'       => $trip,
                'day'        => $day,
                'newItem'    => $item,
                'target'     => 'day_'.$day->getId(),
                'place'      => $item->getPlace(),
            ]);
        }

        return $this->renderBlock('travel_item/create/day_item.html.twig', 'form_modal', [
            'form'       => $form,
            'item'       => $item,
            'formAction' => $request->getUri(),
        ]);
    }

    #[Route('_frame/travel-item/trip/{trip}/create/{type}/{day}', name: 'app_travelitem_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Trip $trip,
        DayRepository $dayRepository,
        TravelItemRepository $travelItemRepository,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
        ?TravelItemType $type = TravelItemType::ACTIVITY,
        ?Day $day = null,
        #[MapQueryParameter] ?string $target = null,
    ): Response
    {
        if ($day && $day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance()->setStatus($status);

        if ($day) $item->setStartDay($day);

        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $position = $position ?? 0;
//
//            $item->setPosition($position);
//            $travelItemRepository->insertItineraryItem($item, $position);
            $entityManager->persist($item);
            $entityManager->flush();

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

//            $response = $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()], Response::HTTP_SEE_OTHER);
//            $response->headers->set('Turbo-Frame', '_top');

//            return $response;


            return $this->renderBlock('travel_item/create.html.twig', 'success_stream');
        }

        return $this->renderBlock('travel_item/create.html.twig', 'form_modal', [
            'form'       => $form,
            'trip'       => $trip,
            'type'       => $type,
            'status'     => $status,
            'day'        => $day,
            'target'     => $target,
        ]);
    }

    #[Route('_frame/travel-item/trip/{trip}/flight/create/', name: 'app_travelitem_create_flight', methods: ['POST', 'GET'])]
    public function createFlight(
        Request $request,
        EntityManagerInterface $entityManager,
        Trip $trip,
        DayRepository $dayRepository,
        TravelItemRepository $travelItemRepository,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::IDEA,
    ): Response
    {
        $type = TravelItemType::FLIGHT;
        $item = $type->createInstance()->setStatus($status);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();

//            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->redirectToRoute('app_trip_show', ['id' => $trip->getId()], Response::HTTP_SEE_OTHER);

//            return $this->renderBlock('travel_item/_create_widget.frame.html.twig', 'success_stream', [
//                'item'       => $item,
//                'trip'       => $trip,
//                'prevItemId' => $prevItemId,
//                'target'     => $target,
//            ]);
        }

        return $this->render('travel_item/flight/turbo/_create_widget.frame.html.twig', [
            'form'       => $form,
            'trip'       => $trip,
            'type'       => $type,
            'status'     => $status,
            'frameId'    => $request->headers->get('Turbo-Frame'),
        ]);
    }
}
