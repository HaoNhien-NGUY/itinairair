<?php

namespace App\Controller;

use App\Entity\Day;
use App\Entity\Trip;
use App\Entity\TripMembership;
use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
use App\Enum\TripRole;
use App\Form\TripType;
use App\Repository\DayRepository;
use App\Repository\TravelItemRepository;
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

    #[Route('/travel-item/trip/{trip}/day/{day}', name: 'app_travelitem_create_day_item', methods: ['POST', 'GET'])]
    public function createDayItem(
        Request $request,
        EntityManagerInterface $entityManager,
        TravelItemRepository $travelItemRepository,
        Trip $trip,
        Day $day,
        #[MapQueryParameter] TravelItemType $type = TravelItemType::ACTIVITY,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::PLANNED,
        #[MapQueryParameter] ?int $prevItemId = null,
        #[MapQueryParameter] ?string $target = null,
    ): Response
    {
        if ($day->getTrip() !== $trip) throw $this->createAccessDeniedException();

        $item = $type->createInstance()->setStatus($status);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($prevItemId !== null) {
                $prevItem = $travelItemRepository->findOneBy(['id' => $prevItemId]);
                $position = $prevItem?->getPosition() + 1 ?: 0;
            }

            $position = $position ?? 0;
            $item->setPosition($position)
                ->setStartDay($day);

            $entityManager->persist($item);
            $entityManager->flush();
            $travelItemRepository->insertAtPosition($item, $position);

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->renderBlock('travel_item/create/day_item.html.twig', 'success_stream', [
                'item'       => $item,
                'trip'       => $trip,
                'prevItemId' => $prevItemId,
                'target'     => $target,
                'place'      => $item->getPlace(),
            ]);
        }

        return $this->renderBlock('travel_item/create/day_item.html.twig', 'form_modal', [
            'form'       => $form,
            'item'       => $item,
            'formAction' => $request->getUri(),
//            'trip'       => $trip,
//            'type'       => $type,
//            'status'     => $status,
//            'prevItemId' => $prevItemId,
//            'target'     => $target,
        ]);
    }

    #[Route('_frame/travel-item/trip/{trip}/create/{type}', name: 'app_travelitem_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Trip $trip,
        DayRepository $dayRepository,
        TravelItemRepository $travelItemRepository,
        #[MapQueryParameter] ItemStatus $status = ItemStatus::IDEA,
        #[MapQueryParameter] ?int $prevItemId = null,
        ?TravelItemType $type = null,
        #[MapQueryParameter] ?int $dayId = null,
        #[MapQueryParameter] ?string $target = null,
    ): Response
    {
        if (!$type) {
            return $this->renderBlock('travel_item/create.html.twig', 'form_modal', [
                'trip'       => $trip,
                'type'       => $type,
                'dayId'      => $dayId,
                'status'     => $status,
                'prevItemId' => $prevItemId,
                'target'     => $target,
            ]);
        }

        $item = $type->createInstance()->setStatus($status);
        $form = $this->createForm($type->getFormType(), $item, ['action' => $request->getUri()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $day = $dayRepository->findOneBy(['id' => $dayId, 'trip' => $trip]);

            if ($prevItemId !== null) {
                $prevItem = $travelItemRepository->findOneBy(['id' => $prevItemId]);
                $position = $prevItem?->getPosition() + 1 ?: 0;
            }

            $position = $position ?? 0;

            $item->setPosition($position)
                ->setStartDay($day);
            $travelItemRepository->insertAtPosition($item, $position);
            $entityManager->persist($item);
            $entityManager->flush();

            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $this->renderBlock('travel_item/create.html.twig', 'success_stream', [
                'item'       => $item,
                'trip'       => $trip,
                'prevItemId' => $prevItemId,
                'target'     => $target,
            ]);
        }

        return $this->renderBlock('travel_item/create.html.twig', 'form_modal', [
            'form'       => $form,
            'trip'       => $trip,
            'type'       => $type,
            'dayId'      => $dayId,
            'status'     => $status,
            'prevItemId' => $prevItemId,
            'target'     => $target,
        ]);
    }
}
