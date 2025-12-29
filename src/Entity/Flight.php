<?php

namespace App\Entity;

use App\Repository\FlightRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlightRepository::class)]
class Flight extends TravelItem
{
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    #[ORM\Column(type: 'json')]
    private array $departureAirport = [];

    #[ORM\Column(type: 'json')]
    private array $arrivalAirport = [];

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(?string $flightNumber): static
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getDepartureAirport(): array
    {
        return $this->departureAirport;
    }

    public function setDepartureAirport(array $departureAirport): static
    {
        $this->departureAirport = $departureAirport;

        return $this;
    }

    public function getArrivalAirport(): array
    {
        return $this->arrivalAirport;
    }

    public function setArrivalAirport(array $arrivalAirport): static
    {
        $this->arrivalAirport = $arrivalAirport;

        return $this;
    }
}
