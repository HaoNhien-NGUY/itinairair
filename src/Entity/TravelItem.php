<?php

namespace App\Entity;

use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
use App\Repository\TravelItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TravelItemRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[ORM\DiscriminatorMap([
    'accommodation' => Accommodation::class,
    'flight' => Flight::class,
    'activity' => Activity::class,
])]
abstract class TravelItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne]
    private ?Day $startDay = null;

    // TODO: relation with a future booking entity ?

    #[ORM\ManyToOne]
    #[Assert\GreaterThan(propertyPath: 'startDay')]
    private ?Day $endDay = null;

    //TODO: add a duration field to the entity

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $endTime = null;

    #[ORM\Column(enumType: ItemStatus::class)]
    private ItemStatus $status;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Place $place = null;

    public function __construct(?Day $startDay = null, ItemStatus $status = ItemStatus::PLANNED)
    {
        $this->startDay = $startDay;
        $this->status = $status;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getItemType(): TravelItemType
    {
        return TravelItemType::fromClass(static::class);
    }

    #[Assert\Callback]
    public function validateTimes(ExecutionContextInterface $context): void
    {
        $startTimeIsNull = $this->startTime === null;
        $endTimeIsNull = $this->endTime === null;

        if ($startTimeIsNull !== $endTimeIsNull) {
            $context->buildViolation('Start time and end time must both be set or both be empty.')
                ->atPath($startTimeIsNull ? 'startTime' : 'endTime')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getStartDay(): ?Day
    {
        return $this->startDay;
    }

    public function setStartDay(?Day $startDay): static
    {
        $this->startDay = $startDay;

        return $this;
    }

    public function getEndDay(): ?Day
    {
        return $this->endDay;
    }

    public function setEndDay(?Day $endDay): static
    {
        $this->endDay = $endDay;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getStatus(): ?ItemStatus
    {
        return $this->status;
    }

    public function setStatus(ItemStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
