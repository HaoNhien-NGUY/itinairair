<?php

namespace App\Controller;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
use App\Repository\TravelItemRepository;
use App\Service\ItineraryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

final class TravelItemController extends AbstractController
{
    #[isGranted('TRIP_EDIT', 'trip')]
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

    #[isGranted('TRIP_EDIT', 'trip')]
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
            $itineraryService->insertTravelItem($item, $day, $position);

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

    #[isGranted('TRIP_EDIT', 'trip')]
    #[Route('/travel-item/trip/{trip}/day/{day}/reorder', name: 'app_travelitem_reorder_day_item', methods: ['POST'])]
    public function reorderDayItem(
        Request $request,
        TravelItemRepository $travelItemRepository,
        TravelItemRepository $itemRepository,
        EntityManagerInterface $entityManager,
        Trip $trip,
        Day $day,
    ): Response
    {
        if ($day->getTrip() !== $trip
            || !$this->isCsrfTokenValid('itinerary_reorder', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $orderedItemsJson = $request->request->get('ordered_items');
        $orderedItems = json_decode($orderedItemsJson) ?? [];

        $items = $itemRepository->findBy([
            'id' => $orderedItems,
            'startDay' => $day,
            'status' => ItemStatus::committed()],
        );
        $itemsById = [];
        foreach ($items as $item) $itemsById[$item->getId()] = $item;

        foreach ($orderedItems as $position => $id) {
            if (!isset($itemsById[$id])) continue;

            $itemsById[$id]->setPosition($position);
        }

        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render('trip/day/_day_update.html.twig', [
            'items'      => $travelItemRepository->findItemsForDay($day),
            'trip'       => $trip,
            'day'        => $day,
        ]);
    }

    #[isGranted('TRIP_EDIT', 'trip')]
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
