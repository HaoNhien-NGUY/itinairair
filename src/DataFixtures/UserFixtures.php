<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const USER_TRIP_ADMIN = 'user_trip_admin';

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@test.com')
            ->setIsVerified(true)
            ->setUsername('TestUser')
            ->setDiscriminator('1234');


        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_TRIP_ADMIN, $user);
    }
}
