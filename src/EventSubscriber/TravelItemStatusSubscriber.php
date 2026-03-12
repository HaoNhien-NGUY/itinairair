<?php

namespace App\EventSubscriber;

use App\Entity\TravelItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class TravelItemStatusSubscriber implements EventSubscriberInterface
{
    public function onWorkflowTravelItemStatusGuard(GuardEvent $event): void
    {
        /** @var TravelItem $item */
        $item = $event->getSubject();

        if (null === $item->getStartDay()) {
            $event->setBlocked(true, 'Item cannot be committed because it doesn\'t have a start day.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GuardEvent::getName('travel_item_status', 'plan') => 'onWorkflowTravelItemStatusGuard',
            GuardEvent::getName('travel_item_status', 'require_booking') => 'onWorkflowTravelItemStatusGuard',
            GuardEvent::getName('travel_item_status', 'book') => 'onWorkflowTravelItemStatusGuard',
        ];
    }
}
