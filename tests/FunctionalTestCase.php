<?php

namespace App\Tests;

use App\Entity\Trip;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Enum\TestUserType;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTestCase extends WebTestCase
{
    protected function getUser(TestUserType $userType = TestUserType::USER): User
    {
        $container = static::getContainer();
        $userRepo = $container->get(UserRepository::class);

        $user = $userRepo->findOneBy(['email' => $userType->getEmail()]);

        if ($user) {
            return $user;
        }

        $em = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail($userType->getEmail())
            ->setIsVerified(true)
            ->setUsername('TestUser'.uniqid())
            ->setDiscriminator('1234');

        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function createAuthenticatedClient(KernelBrowser $client, TestUserType $userType = TestUserType::USER): User
    {
        $user = $this->getUser($userType);
        $client->loginUser($user);

        return $user;
    }

    protected function createTrip(
        User $user,
        ?string $name = null,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null,
    ): Trip {
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $trip = Trip::create($user);

        $trip->setName($name ?? sprintf('Test Trip %s', uniqid()));
        $trip->setStartDate($startDate ?? new \DateTime('+1 day'));
        $trip->setEndDate($endDate ?? new \DateTime('+10 days'));

        $em->persist($trip);
        $em->flush();

        return $trip;
    }
}
