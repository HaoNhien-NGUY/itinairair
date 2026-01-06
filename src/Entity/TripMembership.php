<?php

namespace App\Entity;

use App\Enum\TripRole;
use App\Repository\TripMembershipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TripMembershipRepository::class)]
class TripMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tripMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trip $trip = null;

    #[ORM\ManyToOne(inversedBy: 'tripMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $member = null;

    #[ORM\Column(length: 255, enumType: TripRole::class)]
    private TripRole $role;

    //TODO: if read only mode, default to VIEWER role
    public function __construct(Trip $trip, User $member, TripRole $role = TripRole::EDITOR)
    {
        $this->trip = $trip;
        $this->member = $member;
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getRole(): TripRole
    {
        return $this->role;
    }

    public function setRole(TripRole $role): static
    {
        $this->role = $role;

        return $this;
    }
}
