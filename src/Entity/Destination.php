<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use App\Validator\UniqueDestinationRange;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueDestinationRange]
#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination extends TravelItem
{
    private int $accommodationCount = 0;

    public function __toString(): string
    {
        return $this->getName();
    }

    #[Assert\Callback]
    public function validateEndDate(ExecutionContextInterface $context): void
    {
        if (null === $this->startDay || null === $this->endDay) {
            $context->buildViolation('Une destination doit avoir une date de debut et de fin')
                ->atPath('endDay')
                ->addViolation();

            return;
        }

        if ($this->endDay === $this->startDay) {
            $context->buildViolation('La date de depart de peut etre egale a la date d\'arriveee')
                ->atPath('endDay')
                ->addViolation();
        }
    }

    public function setAccommodationCount(int $count): self
    {
        $this->accommodationCount = $count;

        return $this;
    }

    public function getAccommodationCount(): ?int
    {
        return $this->accommodationCount;
    }
}
