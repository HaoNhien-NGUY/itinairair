<?php

namespace App\Enum;

enum ItemStatus: string
{
    case IDEA = 'idea';
    case PLANNED = 'planned';
    case BOOKING_NEEDED = 'booking_needed';
    case BOOKED = 'booked';

}
