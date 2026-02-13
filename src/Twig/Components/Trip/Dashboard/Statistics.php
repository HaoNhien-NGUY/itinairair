<?php

namespace App\Twig\Components\Trip\Dashboard;

use App\Entity\User;
use App\Repository\DestinationRepository;
use App\Repository\TripMembershipRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Statistics
{
    public User $user;

    private ?int $membershipsCount = null;

    /** @var array{countries: string[], cities: string[]}|null */
    private ?array $stats = null;

    public function __construct(
        private readonly TripMembershipRepository $tripMembershipRepository,
        private readonly DestinationRepository $destinationRepository,
    ) {
    }

    public function getMembershipsCount(): int
    {
        if (null === $this->membershipsCount) {
            $this->membershipsCount = $this->tripMembershipRepository->count(['member' => $this->user]);
        }

        return $this->membershipsCount;
    }

    /**
     * @return array{countries: string[], cities: string[]}
     */
    public function getStats(): array
    {
        if (null === $this->stats) {
            $this->stats = $this->destinationRepository->findDestinationByUser($this->user);
        }

        return $this->stats;
    }
}
