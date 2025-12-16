<?php

namespace App\Validator;

use App\Entity\Destination;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDestinationRangeValidator extends ConstraintValidator
{
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

        if ($startDay === null || $endDay === null) {
            return;
        }

        $startPos = $startDay->getPosition();
        $endPos = $endDay->getPosition();

        foreach ($trip->getDestinations() as $existingDestination) {
            if ($existingDestination === $value) {
                continue;
            }

            $existingStartDay = $existingDestination->getStartDay();
            $existingEndDay = $existingDestination->getEndDay();

            if ($existingStartDay === null || $existingEndDay === null) {
                continue;
            }

            $existingStartPos = $existingStartDay->getPosition();
            $existingEndPos = $existingEndDay->getPosition();

            if (max($startPos, $existingStartPos) < min($endPos, $existingEndPos)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('startDay')
                    ->addViolation();
                return;
            }
        }
    }
}
