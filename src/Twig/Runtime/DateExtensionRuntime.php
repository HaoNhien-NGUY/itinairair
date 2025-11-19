<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class DateExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
    }

    public function formatDateShort(\DateTimeInterface $date): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            'EEE d MMM'
        );

        return $formatter->format($date);
    }
}
