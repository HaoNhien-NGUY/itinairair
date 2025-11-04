<?php

namespace App\Enum;

enum AccommodationType: string
{
    case HOTEL = 'hotel';
    case AIRBNB = 'airbnb';
    case OTHER = 'other';
}
