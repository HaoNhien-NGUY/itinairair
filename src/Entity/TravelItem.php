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
    'destination' => Destination::class,
    'note' => Note::class,
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
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    protected ?Day $startDay = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    protected ?Day $endDay = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $endTime = null;

    #[ORM\Column(enumType: ItemStatus::class)]
    private ItemStatus $status;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Place $place = null;

    #[ORM\ManyToOne(inversedBy: 'travelItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trip $trip = null;

    public function __construct(Trip $trip, ?Day $startDay = null, ItemStatus $status = ItemStatus::PLANNED)
    {
        $this->trip = $trip;
        $this->startDay = $startDay;
        $this->status = $status;
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if (null === $this->startDay || null === $this->endDay) {
            return;
        }

        if ($this->endDay->getPosition() <= $this->startDay->getPosition()) {
            $context->buildViolation('La date de fin doit etre superieur a la date de debut')
                ->atPath('endDay')
                ->addViolation();
        }
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
        $this->notes = trim($notes ?? '');

        return $this;
    }

    public function getStartDay(): ?Day
    {
        return $this->startDay;
    }

    public function setStartDay(?Day $startDay): static
    {
        if ($startDay
            && $this->trip
            && $this->trip !== $startDay->getTrip()) {
                throw new \InvalidArgumentException('You cannot schedule an item on a day from a different trip!');
        }

        if ($startDay && !$this->trip) $this->trip = $startDay->getTrip();

        $this->startDay = $startDay;

        return $this;
    }

    public function getEndDay(): ?Day
    {
        return $this->endDay;
    }

    public function setEndDay(?Day $endDay): static
    {
        if ($endDay
            && $this->trip
            && $this->trip !== $endDay->getTrip()) {
            throw new \InvalidArgumentException('You cannot schedule an item on a day from a different trip!');
        }

        if ($endDay && !$this->trip) $this->trip = $endDay->getTrip();

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

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): static
    {
        $this->trip = $trip;

        return $this;
    }


    public function getDurationInDays(bool $nightCount = false): int
    {
        if (!$this->startDay || !$this->endDay) {
            return 0;
        }

        return $this->endDay->getPosition() - $this->startDay->getPosition() + ($nightCount ? 0 : 1);
    }

}
