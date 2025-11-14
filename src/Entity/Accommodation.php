<?php

namespace App\Entity;

use App\Enum\AccommodationType;
use App\Enum\TripRole;
use App\Repository\AccommodationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccommodationRepository::class)]
class Accommodation extends TravelItem
{
    //TravelItem startdatetime - enddatetime is the checkin - checkout

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bookingReference = null;

    #[ORM\Column(enumType: AccommodationType::class)]
    private AccommodationType $type = AccommodationType::HOTEL;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBookingReference(): ?string
    {
        return $this->bookingReference;
    }

    public function setBookingReference(?string $bookingReference): static
    {
        $this->bookingReference = $bookingReference;

        return $this;
    }

    public function getType(): TripRole
    {
        return $this->type;
    }

    public function setType(TripRole $type): static
    {
        $this->type = $type;

        return $this;
    }
}
