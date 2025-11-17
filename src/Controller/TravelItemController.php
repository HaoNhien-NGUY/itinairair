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

        $item = $type->createInstance([$day, $status]);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itineraryService->insertTravelItem($item, $position, $day);

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('trip/day/_day_update.html.twig', [
                'items'      => $travelItemRepository->findItemsForDay($day),
                'trip'       => $trip,
                'day'        => $day,
                'newItem'    => $item,
            ]);
        }

        return $this->render('travel_item/activity/_create_modal.frame.html.twig', [
            'form'       => $form,
            'item'       => $item,
        ]);
    }

    #[Route('_frame/travel-item/trip/{trip}/create/{type}/{day}', name: 'app_travelitem_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Trip $trip,
        ?TravelItemType $type = TravelItemType::ACTIVITY,
        ?Day $day = null,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
    ): Response
    {
        if ($day && $day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance([$day, $status]);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('stream/refresh.stream.html.twig');
        }

        return $this->render($type->getTemplate(), [
            'form'       => $form,
            'trip'       => $trip,
            'type'       => $type,
            'status'     => $status,
            'day'        => $day,
        ]);
    }
}
