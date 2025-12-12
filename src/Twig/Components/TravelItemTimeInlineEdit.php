<?php

namespace App\Twig\Components;

use App\Entity\TravelItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TravelItemTimeInlineEdit
{
    use DefaultActionTrait;

    #[LiveProp]
    public bool $isEditing = false;

    #[LiveProp]
    public TravelItem $item;

    #[LiveProp(writable: true)]
    public ?string $startTime = null;

    #[LiveProp(writable: true)]
    public ?string $endTime = null;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function mount(TravelItem $item): void
    {
        $this->item = $item;
        $this->startTime = $item->getStartTime()?->format('H:i');
        $this->endTime = $item->getEndTime()?->format('H:i');
    }

    #[LiveAction]
    public function save(): void
    {
        $this->item->setStartTime($this->startTime ? new \DateTime($this->startTime) : null);
        $this->item->setEndTime($this->endTime ? new \DateTime($this->endTime) : null);

        $this->entityManager->flush();
        $this->isEditing = false;
    }

    #[LiveAction]
    public function activateEditing(): void
    {
        $this->isEditing = true;
    }

    #[LiveAction]
    public function cancel(): void
    {
        $this->isEditing = false;
    }
}
