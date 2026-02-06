<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Destination>
 */
class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    /**
     * @return Destination[]
     */
    public function findDestinationByDay(Day $day): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.startDay', 'sd')
            ->join('d.endDay', 'ed')
            ->where('d.trip = :trip')
            ->andWhere('sd.position <= :position')
            ->andWhere('ed.position >= :position')
            ->setParameter('trip', $day->getTrip())
            ->setParameter('position', $day->getPosition())
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Destination[]
     */
    public function findDestinationByTrip(Trip $trip): array
    {
        $results = $this->createQueryBuilder('d')
            ->addSelect('sd', 'ed', 'p')
            ->addSelect('(
        SELECT COUNT(i.id)
        FROM App\Entity\Accommodation i
        JOIN i.startDay isd
        JOIN i.place ip
        WHERE i.trip = :trip
        AND isd.position >= sd.position
        AND (ed.id IS NULL OR isd.position < ed.position)
        AND ip.country = p.country
    ) AS acc_count')
            ->where('d.trip = :trip')
            ->setParameter('trip', $trip)
            ->join('d.startDay', 'sd')
            ->leftJoin('d.endDay', 'ed')
            ->leftJoin('d.place', 'p')
            ->orderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function ($result) {
            /** @var Destination $dest */
            $dest = $result[0];
            $dest->setAccommodationCount($result['acc_count']);

            return $dest;
        }, $results);
    }

    /**
     * @return array{byStartDay: Destination[], byEndDay: Destination[]}
     */
    public function findDestinationsMappedByDayPosition(Trip $trip): array
    {
        $destinations = $this->findDestinationByTrip($trip);

        $map = [
            'byStartDay' => [],
            'byEndDay'   => [],
        ];

        foreach ($destinations as $dest) {
            if ($dest->getStartDay()) {
                $map['byStartDay'][$dest->getStartDay()->getPosition()] = $dest;
            }

            if ($dest->getEndDay()) {
                $map['byEndDay'][$dest->getEndDay()->getPosition()] = $dest;
            }
        }

        return $map;
    }

    /**
     * @return string[]
     */
    public function findDestinationCountriesByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('d')
            ->select('p.country')
            ->where('d.trip = :trip')
            ->setParameter('trip', $trip)
            ->leftJoin('d.place', 'p')
            ->leftJoin('d.startDay', 'sd')
            ->groupBy('p.country')
            ->orderBy('MIN(sd.position)', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @param int[] $tripsIds
     *
     * @return array<int, string[]>
     */
    public function findDestinationCountriesByTrips(array $tripsIds): array
    {
        if (empty($tripsIds)) {
            return [];
        }

        $result = $this->createQueryBuilder('d')
            ->select('IDENTITY(d.trip) AS trip_id, p.country')
            ->where('d.trip in (:trips)')
            ->setParameter('trips', $tripsIds)
            ->join('d.trip', 't')
            ->leftJoin('d.place', 'p')
            ->leftJoin('d.startDay', 'sd')
            ->groupBy('p.country', 'd.trip')
            ->orderBy('MIN(sd.position)', 'ASC')
            ->getQuery()
            ->getResult();

        $output = [];

        foreach ($result as $row) {
            $output[$row['trip_id']][] = $row['country'];
        }

        return $output;
    }

    /**
     * @return array{countries: string[], cities: string[]}
     */
    public function findDestinationByUser(UserInterface $user): array
    {
        $results = $this->createQueryBuilder('d')
            ->select('p.country', 'p.city')
            ->leftJoin('d.trip', 't')
            ->leftJoin('d.place', 'p')
            ->leftJoin('d.startDay', 'sd')
            ->join('t.tripMemberships', 'tm')
            ->where('tm.member = :user')
            ->setParameter('user', $user)
            ->groupBy('p.country', 'p.city')
            ->orderBy('MIN(sd.position)', 'ASC')
            ->getQuery()
            ->getResult();

        return [
            'countries' => array_unique(array_column($results, 'country')),
            'cities'    => array_unique(array_column($results, 'city')),
        ];
    }

    /**
     * @return string[]
     */
    public function findDestinationCitiesByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('d')
            ->select('p.city')
            ->where('d.trip = :trip')
            ->setParameter('trip', $trip)
            ->leftJoin('d.place', 'p')
            ->leftJoin('d.startDay', 'sd')
            ->groupBy('p.city')
            ->orderBy('MIN(sd.position)', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function countOverlappingDestinations(Trip $trip, int $startPos, int $endPos, ?Destination $excludeDestination = null): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->join('d.startDay', 'sd')
            ->join('d.endDay', 'ed')
            ->where('d.trip = :trip')
            ->andWhere('sd.position < :endPos')
            ->andWhere('ed.position > :startPos')
            ->setParameter('trip', $trip)
            ->setParameter('startPos', $startPos)
            ->setParameter('endPos', $endPos);

        if ($excludeDestination && $excludeDestination->getId()) {
            $qb->andWhere('d.id != :excludeId')
                ->setParameter('excludeId', $excludeDestination->getId());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
