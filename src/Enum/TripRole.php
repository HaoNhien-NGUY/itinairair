<?php

namespace App\Enum;

enum TripRole: string
{
    case ADMIN = 'admin';
    case VIEWER = 'viewer';
    case EDITOR = 'editor';
}
