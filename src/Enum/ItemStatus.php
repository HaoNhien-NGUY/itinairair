<?php

namespace App\Enum;

enum ItemStatus: string
{
    case IDEA = 'idea';
    case PLANNED = 'planned';
    case BOOKING_NEEDED = 'booking_needed';
    case BOOKED = 'booked';

    public static function committed(): array
    {
        return array_filter(
            self::cases(),
            fn(self $status) => $status !== self::IDEA
        );
    }

    public static function draft(): array
    {
        return [self::IDEA];
    }
}
