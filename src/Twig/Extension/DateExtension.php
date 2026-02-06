<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\DateExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('date_short', [DateExtensionRuntime::class, 'formatDateShort'], ['is_safe' => ['html']]),
            new TwigFilter('date_xs', [DateExtensionRuntime::class, 'formatDateXs'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
        ];
    }
}
