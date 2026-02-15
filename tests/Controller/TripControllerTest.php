<?php

namespace App\Tests\Controller;

use App\Repository\TripRepository;
use App\Tests\Enum\TestUserType;
use App\Tests\FunctionalTestCase;

class TripControllerTest extends FunctionalTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client);

        $client->request('GET', '/trip/');

        $this->assertResponseIsSuccessful();
    }

    public function testNotLogged(): void
    {
        $client = static::createClient();

        $user = $this->getUser();
        $trip = $this->createTrip($user);

        $client->request('GET', sprintf('/trip/edit/%s', $trip->getId()));

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testNotTripMember(): void
    {
        $client = static::createClient();

        $tripMember = $this->getUser(TestUserType::USER_2);
        $trip = $this->createTrip($tripMember);

        $this->createAuthenticatedClient($client);

        $client->request('GET', sprintf('/trip/%s', $trip->getId()));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateTrip(): void
    {
        $client = static::createClient();
        $this->createAuthenticatedClient($client);

        $client->request('GET', '/trip/create');
        $this->assertResponseIsSuccessful();

        $tripName = 'Test Trip';
        $startDate = new \DateTime('+1 day');
        $endDate = new \DateTime('+10 days');

        $client->submitForm('trip[save]', [
            'trip[name]' => $tripName,
            'trip[startDate]' => $startDate->format('Y-m-d'),
            'trip[endDate]' => $endDate->format('Y-m-d'),
            'trip[description]' => 'test trip description',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', $tripName);
    }

    public function testEditTrip(): void
    {
        $client = static::createClient();
        $user = $this->createAuthenticatedClient($client);

        $trip = $this->createTrip($user);

        $client->request('GET', sprintf('/trip/edit/%s', $trip->getId()));
        $this->assertResponseIsSuccessful();

        $newName = 'New Test Trip';

        $client->submitForm('trip[save]', [
            'trip[name]' => $newName,
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', $newName);
    }

    public function testDeleteTrip(): void
    {
        $client = static::createClient();
        $user = $this->createAuthenticatedClient($client);

        $trip = $this->createTrip($user);
        $tripId = $trip->getId();

        $client->request('GET', sprintf('/trip/edit/%s', $tripId));
        $this->assertResponseIsSuccessful();

        $client->submitForm('trip-delete');

        $this->assertResponseRedirects('/trip/');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $tripCheck = static::getContainer()->get(TripRepository::class)->find($tripId);
        $this->assertNull($tripCheck);
    }
}
