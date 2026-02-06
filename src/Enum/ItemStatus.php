<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ItemStatus: string implements TranslatableInterface
{
    case IDEA = 'idea';
    case PLANNED = 'planned';
    case BOOKING_NEEDED = 'booking_needed';
    case BOOKED = 'booked';

    /** @return array<self> */
    public static function committed(): array
    {
        return [
            self::PLANNED,
            self::BOOKING_NEEDED,
            self::BOOKED
        ];
    }

    /** @return array<self> */
    public static function draft(): array
    {
        return [self::IDEA];
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('travel_items.status.' . $this->value, locale: $locale);

    }
}
