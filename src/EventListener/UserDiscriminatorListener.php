<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\DiscriminatorGenerator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
readonly class UserDiscriminatorListener
{
    public function __construct(
        private DiscriminatorGenerator $generator
    ) {}

    public function prePersist(User $user): void
    {
        if ($user->getDiscriminator()) {
            return;
        }

        $discriminator = $this->generator->generateDiscriminator($user->getUsername());
        $user->setDiscriminator($discriminator);
    }
}
