<?php

namespace App\Tests;

use App\DataFixtures\TripFixtures;
use App\Repository\TripRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    #[DataProvider('publicUrlProvider')]
    public function testPublicPageIsSuccessful(string $url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public static function publicUrlProvider(): \Generator
    {
        yield ['/'];
        yield ['/login'];
        yield ['/register'];
    }

    #[DataProvider('loggedInUrlProvider')]
    public function testSecuredPagesIsSuccessful(string $url, ?string $tripName): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);

        $user = $userRepository->findOneBy(['email' => 'test@test.com']);

        if (!$user) {
            $this->markTestSkipped('No test user found.');
        }

        if ($tripName) {
            $trip = $container->get(TripRepository::class)->findOneBy(['name' => $tripName]);

            if (!$trip) {
                $this->markTestSkipped('No test trip found.');
            }

            $url = sprintf($url, $trip->getId());
        }

        $client->loginUser($user);

        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }


    public static function loggedInUrlProvider(): \Generator
    {
        yield ['/trip', null];
        yield ['/account', null];
        yield ['/trip/%s', TripFixtures::TITLE_PARIS];
        yield ['/trip/%s/itinerary', TripFixtures::TITLE_PARIS];
        yield ['/trip/%s/bookings', TripFixtures::TITLE_PARIS];
    }
}
