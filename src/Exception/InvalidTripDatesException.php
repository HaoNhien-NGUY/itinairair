<?php

namespace App\Exception;

class InvalidTripDatesException extends \LogicException
{
    public function __construct(string $message = "Invalid trip dates provided.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
