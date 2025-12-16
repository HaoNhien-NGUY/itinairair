<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueDestinationRange extends Constraint
{
    public string $message = 'Les dates de cette destination chevauchent une autre destination de ce voyage.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
