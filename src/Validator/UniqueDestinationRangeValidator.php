<?php

namespace App\Validator;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDestinationRangeValidator extends ConstraintValidator
{
    public function __construct(private readonly DestinationRepository $destinationRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDestinationRange) {
            throw new UnexpectedTypeException($constraint, UniqueDestinationRange::class);
        }

        if (!$value instanceof Destination) {
            throw new UnexpectedTypeException($value, Destination::class);
        }

        $trip = $value->getTrip();
        if (!$trip) {
            return;
        }

        $startDay = $value->getStartDay();
        $endDay = $value->getEndDay();

        if (null === $startDay || null === $endDay) {
            return;
        }

        $startPos = $startDay->getPosition();
        $endPos = $endDay->getPosition();

        $overlappingCount = $this->destinationRepository->countOverlappingDestinations(
            $trip,
            $startPos,
            $endPos,
            $value
        );

        if ($overlappingCount > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('startDay')
                ->addViolation();
        }
    }
}
