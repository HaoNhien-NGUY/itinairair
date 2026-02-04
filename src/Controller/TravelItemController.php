<?php

namespace App\Controller;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
use App\Repository\DestinationRepository;
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
    #[Route('/travel-item/trip/{trip}/delete', name: 'app_travelitem_delete', methods: ['POST'])]
    public function delete(Request $request, Trip $trip, TravelItemRepository $itemRepository, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-item' . $trip->getId(), $request->request->get('_token'))) {
            $itemId = $request->request->get('item');
            $item = $itemRepository->findOneBy(['id' => $itemId]);

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
        DestinationRepository $destinationRepository,
        ItineraryService $itineraryService,
        Trip $trip,
        Day $day,
        #[MapQueryParameter] TravelItemType $type = TravelItemType::ACTIVITY,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
        #[MapQueryParameter] ?int $position = 0,
    ): Response {
        if ($day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance([$trip, $day, $status]);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itineraryService->insertTravelItem($item, $day, $position);

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->render('trip/day/_day_update.html.twig', [
                'trip'       => $trip,
                'day'        => $day,
                'newItem'    => $item,
            ]);
        }

        $destination = $destinationRepository->findDestinationByDay($day)[0] ?? null;

        return $this->render($type->getFormTemplate(), [
            'form'        => $form,
            'item'        => $item,
            'destination' => $destination,
        ]);
    }

    #[isGranted('TRIP_EDIT', 'trip')]
    #[Route('/travel-item/trip/{trip}/day/{day}/reorder', name: 'app_travelitem_reorder_day_item', methods: ['POST'])]
    public function reorderDayItem(
        Request $request,
        TravelItemRepository $itemRepository,
        EntityManagerInterface $entityManager,
        Trip $trip,
        Day $day,
    ): Response {
        if (
            $day->getTrip() !== $trip
            || !$this->isCsrfTokenValid('itinerary_reorder', $request->request->get('_token'))
        ) {
            throw $this->createAccessDeniedException();
        }

        $orderedItemsJson = $request->request->get('ordered_items');
        $orderedItems = json_decode($orderedItemsJson) ?? [];
        $daysToUpdate = [$day];

        $items = $itemRepository->findBy(
            [
                'id' => $orderedItems,
                'startDay' => $day,
                'status' => ItemStatus::committed()
            ],
        );
        $itemsById = [];
        foreach ($items as $item) $itemsById[$item->getId()] = $item;

        foreach ($orderedItems as $position => $id) {
            if (isset($itemsById[$id])) {
                $itemsById[$id]->setPosition($position);

                continue;
            }

            $newItem = $itemRepository->findOneBy(['id' => $id, 'trip' => $trip]);

            if (!$newItem) continue;
            if ($dayToUpdate = $newItem->getStartDay()) $daysToUpdate[] = $dayToUpdate;

            $newItem->setPosition($position)
                ->setStartDay($day);

            if (in_array($newItem->getStatus(), ItemStatus::draft())) {
                $newItem->setStatus(ItemStatus::PLANNED);
            }
        }

        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render('trip/day/_batch_day_update.stream.html.twig', [
            'trip' => $trip,
            'daysToUpdate' => $daysToUpdate,
        ]);
    }

    #[isGranted('TRIP_EDIT', 'trip')]
    #[Route('/travel-item/trip/{trip}/to-idea', name: 'app_travelitem_item_to_idea', methods: ['POST'])]
    public function itemToIdea(
        Request $request,
        TravelItemRepository $itemRepository,
        EntityManagerInterface $entityManager,
        Trip $trip,
    ): Response {
        if (!$this->isCsrfTokenValid('item_to_idea', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $itemId = $request->request->get('item');
        $item = $itemRepository->findOneBy(['id' => $itemId, 'trip' => $trip, 'status' => ItemStatus::committed()]);
        $daysToUpdate = [];

        if ($item) {
            $daysToUpdate[] = $item->getStartDay();

            $item->setPosition(null)
                ->setStatus(ItemStatus::IDEA)
                ->setStartDay(null);
        }

        $entityManager->flush();

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return $this->render('trip/day/_item_to_idea.stream.html.twig', [
            'daysToUpdate' => $daysToUpdate,
            'trip' => $trip,
        ]);
    }

    #[isGranted('TRIP_EDIT', 'trip')]
    #[Route('_frame/travel-item/trip/{trip}/create/{type}/{day}', name: 'app_travelitem_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        DestinationRepository $destinationRepository,
        Trip $trip,
        ?TravelItemType $type = TravelItemType::ACTIVITY,
        ?Day $day = null,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
        #[MapQueryParameter] bool $overnight = false,
    ): Response {
        if ($day && $day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance([$trip, $day, $status]);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri(), 'trip' => $trip]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('stream/refresh.stream.html.twig');
        }

        $destination = $day ? ($destinationRepository->findDestinationByDay($day)[0] ?? null) : null;

        return $this->render($type->getFormTemplate(), [
            'form'       => $form,
            'trip'       => $trip,
            'item'       => $item,
            'type'       => $type,
            'status'     => $status,
            'day'        => $day,
            'overnight'  => $overnight,
            'destination' => $destination,
        ]);
    }

    #[isGranted('TRIP_EDIT', 'trip')]
    #[Route('_frame/travel-item/trip/{trip}/{item}/edit', name: 'app_travelitem_edit', methods: ['POST', 'GET'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Trip $trip,
        TravelItem $item,
    ): Response {
        if ($item->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $type = $item->getItemType();
        $form = $this->createForm($type->getFormType(), $item, [
            'action' => $request->getUri(),
            'trip' => $trip
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('stream/refresh.stream.html.twig');
        }

        return $this->render($type->getFormTemplate(), [
            'form'       => $form,
            'trip'       => $trip,
            'type'       => $type,
            'item'       => $item,
            'status'     => $item->getStatus(),
            'day'        => $item->getStartDay(),
            'endDay'     => $item->getEndDay(),
        ]);
    }
}
