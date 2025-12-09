<?php

namespace App\Twig\Components;

use App\Entity\TravelItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class TravelItemNoteInlineEdit
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $notes = null;

    #[LiveProp]
    public TravelItem $item;

    #[LiveProp(writable: true)]
    public bool $isEditing = false;

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function mount(TravelItem $item): void
    {
        $this->item = $item;
        $this->notes = $item->getNotes();
    }

    #[LiveAction]
    public function save(): void
    {
        $this->item->setNotes($this->notes);
        $this->entityManager->flush();
        $this->isEditing = false;
    }

    #[LiveAction]
    public function enableEdit(): void
    {
        $this->isEditing = true;
    }

    #[LiveAction]
    public function cancel(): void
    {
        $this->notes = $this->item->getNotes();
        $this->isEditing = false;
    }
}
