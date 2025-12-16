<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use App\Validator\UniqueDestinationRange;
use Doctrine\ORM\Mapping as ORM;

#[UniqueDestinationRange]
#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination extends TravelItem
{
    public function __toString(): string
    {
        return $this->getName();
    }
}
