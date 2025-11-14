<?php

namespace App\Entity;

use App\Repository\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripRepository::class)]
class Trip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Day>
     */
    #[ORM\OneToMany(targetEntity: Day::class, mappedBy: 'trip', cascade: ['persist', 'remove'] , orphanRemoval: true)]
    private Collection $days;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

//    TODO: maybe remove endDate and replace it with nbDays. In the form if a endDate is given, calculate the number of days in PRE_SUBMIT, maybe create a twig filter to get the endDate from a trip
//    ask user if endDate is set, if not ask an estimated nb of days
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?\DateTime $endDate = null;

    /**
     * @var Collection<int, TripMembership>
     */
    #[ORM\OneToMany(targetEntity: TripMembership::class, mappedBy: 'trip', orphanRemoval: true)]
    private Collection $tripMemberships;

    public function __construct()
    {
        $this->days = new ArrayCollection();
        $this->tripMemberships = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Day>
     */
    public function getDays(): Collection
    {
        return $this->days;
    }

    public function addDay(Day $day): static
    {
        if (!$this->days->contains($day)) {
            $this->days->add($day);
            $day->setTrip($this);
        }

        return $this;
    }

    public function removeDay(Day $day): static
    {
        if ($this->days->removeElement($day)) {
            // set the owning side to null (unless already changed)
            if ($day->getTrip() === $this) {
                $day->setTrip(null);
            }
        }

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, TripMembership>
     */
    public function getTripMemberships(): Collection
    {
        return $this->tripMemberships;
    }

    public function addTripMembership(TripMembership $tripMembership): static
    {
        if (!$this->tripMemberships->contains($tripMembership)) {
            $this->tripMemberships->add($tripMembership);
            $tripMembership->setTrip($this);
        }

        return $this;
    }

    public function removeTripMembership(TripMembership $tripMembership): static
    {
        if ($this->tripMemberships->removeElement($tripMembership)) {
            // set the owning side to null (unless already changed)
            if ($tripMembership->getTrip() === $this) {
                $tripMembership->setTrip(null);
            }
        }

        return $this;
    }

    public function getDayMapping(): array
    {
        $mapping = [];

        foreach ($this->days as $day) {
            $mapping[$day->getDate()->format('Y-m-d')] = $day->getId();
        }

        return $mapping;
    }
}
