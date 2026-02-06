<?php

namespace App\Entity;

use App\Repository\FlightRepository;
use Doctrine\ORM\Mapping as ORM;

/** @phpstan-type AirportShape array{code?: string, terminal?: string} */
#[ORM\Entity(repositoryClass: FlightRepository::class)]
class Flight extends TravelItem
{
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flightNumber = null;

    /** @var AirportShape|array{} $departureAirport */
    #[ORM\Column(type: 'json')]
    private array $departureAirport = [];

    /** @var AirportShape|array{} $arrivalAirport */
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

    /** @return AirportShape|array{} */
    public function getDepartureAirport(): array
    {
        return $this->departureAirport;
    }

    /** @param  AirportShape|array{} $departureAirport */
    public function setDepartureAirport(array $departureAirport): static
    {
        $this->departureAirport = $departureAirport;

        return $this;
    }

    /** @return  AirportShape|array{} */
    public function getArrivalAirport(): array
    {
        return $this->arrivalAirport;
    }

    /** @param  AirportShape|array{} $arrivalAirport */
    public function setArrivalAirport(array $arrivalAirport): static
    {
        $this->arrivalAirport = $arrivalAirport;

        return $this;
    }
}
