<?php

namespace App\Service;

use App\Repository\UserRepository;

readonly class DiscriminatorGenerator
{
    private \Closure $randomFn;

    public function __construct(
        private UserRepository $userRepository,
        ?\Closure $randomGenerator = null,
    ) {
        $this->randomFn = $randomGenerator ?? fn (int $min, int $max) => mt_rand($min, $max);
    }

    public function generateDiscriminator(string $username): string
    {
        $taken = $this->userRepository->findDiscriminatorsByUsername($username);

        $attempts = 0;
        do {
            $rand = str_pad((string) ($this->randomFn)(1, 9999), 5, '0', STR_PAD_LEFT);
            ++$attempts;

            if ($attempts > 100) {
                throw new \RuntimeException('Too many users have this username!');
            }
        } while (in_array($rand, $taken));

        return $rand;
    }
}
