<?php

namespace App\Service;

use App\Entity\Accommodation;
use App\Entity\Activity;
use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\Note;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Entity\User;
use App\Enum\ItemStatus;
use App\Event\DemoCreatedEvent;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DemoGeneratorService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PlaceRepository $placeRepository,
        private TranslatorInterface $translator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function generateDemo(): User
    {
        $user = $this->generateDemoUser();
        $this->generateDemoTrip($user);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new DemoCreatedEvent($user));

        return $user;
    }

    public function generateDemoUser(): User
    {
        $user = (new User())
            ->setExpiresAt(new \DateTimeImmutable('+1 hour'))
            ->setEmail('demo_'.uniqid().'@temp')
            ->setUsername($this->translator->trans('trip.demo.username'));

        $this->entityManager->persist($user);

        return $user;
    }

    public function generateDemoTrip(User $user): Trip
    {
        $trip = Trip::create($user)
            ->setName($this->translator->trans('trip.demo.name'))
            ->setStartDate(new \DateTime('-4 days'))
            ->setEndDate(new \DateTime('+12 days'));

        $this->entityManager->persist($trip);

        $requiredDays = $trip->getStartDate()->diff($trip->getEndDate())->days + 1;
        $itineraryData = $this->getItineraryData($trip);

        for ($i = 0; $i < $requiredDays; ++$i) {
            $day = (new Day())->setPosition($i + 1);
            $trip->addDay($day);

            foreach (($itineraryData[$i]['start'] ??= []) as $key => $travelItem) {
                $this->entityManager->persist($travelItem);
                $travelItem->setPosition($key);
                $travelItem->setStartDay($day);
                $trip->addTravelItem($travelItem);
            }

            foreach (($itineraryData[$i]['end'] ??= []) as $travelItem) {
                $this->entityManager->persist($travelItem);
                $travelItem->setEndDay($day);
                $trip->addTravelItem($travelItem);
            }
        }


        foreach ($trip->getDays() as $day) {
            $day->setDate($day->getComputedDate());
        }

        return $trip;
    }

    /**
     * @return list<array{start?: list<TravelItem>, end?: list<TravelItem>}>
     */
    private function getItineraryData(Trip $trip): array
    {
        $places = $this->placeRepository->createQueryBuilder('p', 'p.placeId')
            ->where('p.placeId IN (:ids)')
            ->setParameter(
                'ids',
                [
                    'ChIJS-MV5W_zGGARBMfDgNcz-u4',
                    'ChIJRY9wBDmPGGARQRD-fJ5Vnkc',
                    'ChIJXSModoWLGGARILWiCfeu2M0',
                    'ChIJ8cM8zdaoAWARPR27azYdlsA',
                    'ChIJP9eKBdeMGGAR0zzBXJNVj5A',
                    'ChIJPd37MMGOGGARvJ2hfxoiNVE',
                    'ChIJnctryNSMGGARLv4MknPFseU',
                    'ChIJjzOyEQCLGGAR8BA5HSSfpMo',
                    'ChIJSeco5wiJGGARItbTS8lQ5G0',
                    'ChIJw2qQRZuOGGARWmROEiM2y7E',
                    'ChIJd9pWqK8IAWAR1L-X_-4WKew',
                    'ChIJIW0uPRUPAWAR6eI6dRzKGns',
                ],
            )
            ->getQuery()
            ->getResult();

        $idea1 = (new Activity($trip, status: ItemStatus::IDEA))->setName('Fushimi Inari-taisha')->setPlace($places['ChIJIW0uPRUPAWAR6eI6dRzKGns']);
        $this->entityManager->persist($idea1);
        $trip->addTravelItem($idea1);

        $departureFlight = (new Flight($trip))
            ->setName('AF274')
            ->setFlightNumber('AF274')
            ->setStartTime(new \DateTime('21:55:00'))
            ->setEndTime(new \DateTime('19:25:00'))
            ->setDepartureAirport(['code' => 'CDG', 'terminal' => '2E'])
            ->setArrivalAirport(['code' => 'HND', 'terminal' => '3']);
        $acc1 = (new Accommodation($trip))->setName('Daiwa Roynet Hotel Nishi-Shinjuku Premier')->setPlace($places['ChIJS-MV5W_zGGARBMfDgNcz-u4']);
        $acc2 = (new Accommodation($trip))->setName('Far East Village Hotel Tokyo, Asakusa')->setPlace($places['ChIJRY9wBDmPGGARQRD-fJ5Vnkc']);
        $dest1 = (new Destination($trip))->setName('Tokyo')->setPlace($places['ChIJXSModoWLGGARILWiCfeu2M0']);
        $dest2 = (new Destination($trip))->setName('Kyoto')->setPlace($places['ChIJ8cM8zdaoAWARPR27azYdlsA']);

        return [
            [
                'start' => [
                    $departureFlight,
                ],
            ],
            [
                'start' => [
                    $acc1,
                    $dest1,
                    (new Note($trip))->setName('Pocket Wi-Fi !')->setNotes('Ne pas oublier de retirer le Pocket Wi-Fi au comptoir de l\'aéroport.'),
                    (new Activity($trip))->setName('Omoide Yokocho Memory Lane')->setPlace($places['ChIJP9eKBdeMGGAR0zzBXJNVj5A']),
                ],
                'end' => [
                    $departureFlight,
                ],
            ],
            [
                'start' => [
                    (new Activity($trip))->setName('Nakamise Shopping Street')->setPlace($places['ChIJPd37MMGOGGARvJ2hfxoiNVE']),
                    (new Note($trip))->setName('Pause Gourmande')->setNotes('Goûter un Melon Pan à Asakusa.'),
                    (new Activity($trip))->setName('Tokyo Metropolitan Government Building North Observation Deck')
                        ->setPlace($places['ChIJnctryNSMGGARLv4MknPFseU'])
                        ->setStartTime(new \DateTime('17:30:00'))
                        ->setNotes('L\'accès est gratuit, monter à la tour Sud pour la meilleure vue.'),
                ],
            ],
            [
                'start' => [
                    $acc2,
                    (new Activity($trip))->setName('Tsukiji Fish Market')->setPlace($places['ChIJjzOyEQCLGGAR8BA5HSSfpMo']),
                ],
                'end' => [
                    $acc1,
                ],
            ],
            [
                'start' => [
                    (new Note($trip))->setName('Tenue vestimentaire')->setNotes('Prévoir un pantalon relevable pour TeamLab.'),
                    (new Activity($trip))
                        ->setName('teamLab Planets TOKYO DMM')
                        ->setPlace($places['ChIJSeco5wiJGGARItbTS8lQ5G0'])
                        ->setNotes('Télécharger l\'application TeamLab pour interagir avec les œuvres.'),
                ],
            ],
            [
                'start' => [
                ],
            ],
            [
                'start' => [
                    $dest2,
                    (new Activity($trip))->setName('Tour de Kyoto')->setPlace($places['ChIJd9pWqK8IAWAR1L-X_-4WKew']),
                ],
                'end' => [
                    $acc2,
                    $dest1,
                ],
            ],
            [],
            [],
            [
                'end' => [
                    $dest2,
                ],
            ],
        ];

    }
}
