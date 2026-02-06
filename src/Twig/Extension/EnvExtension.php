<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EnvExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [$this, 'getEnvironmentVariable']),
        ];
    }

    public function getEnvironmentVariable(string $var): ?string
    {
        return $_ENV[$var] ?? null;
    }
}
