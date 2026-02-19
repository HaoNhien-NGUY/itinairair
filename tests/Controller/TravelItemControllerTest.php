<?php

namespace App\Tests\Controller;

use App\Entity\Day;
use App\Enum\TravelItemType;
use App\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TravelItemControllerTest extends FunctionalTestCase
{
    /** @param array<string> $fieldsToRemove */
    #[DataProvider('travelItemProvider')]
    public function testCreateItem(TravelItemType $travelItemType, bool $withPlace = false, ?array $fieldsToRemove = []): void
    {
        $client = static::createClient();
        $user = $this->createAuthenticatedClient($client);
        $trip = $this->createTrip($user);
        $em = static::getContainer()->get('doctrine')->getManager();

        $startDay = (new Day())->setTrip($trip)->setPosition(1)->setDate($trip->getStartDate());
        $endDay = (new Day())->setTrip($trip)->setPosition(10)->setDate($trip->getEndDate());
        $em->persist($startDay);
        $em->persist($endDay);
        $em->flush();

        $client->request(
            'GET',
            sprintf('_frame/travel-item/trip/%s/create/%s', $trip->getId(), $travelItemType->value),
        );
        $this->assertResponseIsSuccessful();

        $formData = [
            $travelItemType->value.'[startDay]' => $startDay->getId(),
            $travelItemType->value.'[endDay]'   => $endDay->getId(),
            ...($withPlace ? $this->getPlaceFormData($travelItemType->value) : []),
        ];

        foreach ($fieldsToRemove as $fieldSuffix) {
            $key = $travelItemType->value.'['.$fieldSuffix.']';

            if (array_key_exists($key, $formData)) {
                unset($formData[$key]);
            }
        }

        $client->submitForm('travelItem[save]', $formData);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-stream[action="refresh"]');
    }

    public static function travelItemProvider(): \Generator
    {
        yield TravelItemType::ACCOMMODATION->value => [
            'travelItemType' => TravelItemType::ACCOMMODATION,
            'withPlace' => true,
        ];
        yield TravelItemType::NOTE->value => [
            'travelItemType' => TravelItemType::NOTE,
            'fieldsToRemove' => ['startDay', 'endDay'],
        ];
        yield TravelItemType::ACTIVITY->value => [
            'travelItemType' => TravelItemType::ACTIVITY,
            'fieldsToRemove' => ['startDay', 'endDay'],
            'withPlace' => true,
        ];
        yield TravelItemType::FLIGHT->value => [
            'travelItemType' => TravelItemType::FLIGHT,
        ];
        yield TravelItemType::DESTINATION->value => [
            'travelItemType' => TravelItemType::DESTINATION,
            'withPlace' => true,
        ];
    }

    /** @return array<string, string> */
    private function getPlaceFormData(string $formName): array
    {
        return [
            $formName.'[place][name]'          => 'Eiffel Tower',
            $formName.'[place][address]'       => 'Champ de Mars, 5 Av. Anatole France',
            $formName.'[place][location]'      => '{}',
            $formName.'[place][placeId]'       => '111',
            $formName.'[place][city]'          => 'Paris',
            $formName.'[place][country]'       => 'France',
        ];
    }
}
