<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 500)]
    private ?string $address = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $googleMapsURI = null;

    #[ORM\Column]
    private array $location = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $photoURI = null;

    #[ORM\Column(length: 255)]
    private ?string $placeId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getGoogleMapsURI(): ?string
    {
        return $this->googleMapsURI;
    }

    public function setGoogleMapsURI(?string $googleMapsURI): static
    {
        $this->googleMapsURI = $googleMapsURI;

        return $this;
    }

    public function getLocation(): array
    {
        return $this->location;
    }

    public function setLocation(array $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getPhotoURI(): ?string
    {
        return $this->photoURI;
    }

    public function setPhotoURI(?string $photoURI): static
    {
        $this->photoURI = $photoURI;

        return $this;
    }

    public function getPlaceId(): ?string
    {
        return $this->placeId;
    }

    public function setPlaceId(string $placeId): static
    {
        $this->placeId = $placeId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
