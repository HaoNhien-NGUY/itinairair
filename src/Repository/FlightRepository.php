<?php

namespace App\Repository;

use App\Entity\Flight;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Flight>
 */
class FlightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flight::class);
    }

    public function countFlightsByTrip(Trip $trip): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.trip = :trip')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Flight[] */
    public function findFlightsByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.startDay', 'sd')
            ->where('f.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('sd.position', 'ASC')
            ->addOrderBy('f.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Flight[] */
    public function findOverNightFlightsByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.startDay', 'sd')
            ->where('sd.trip = :trip')
            ->andWhere('f.startDay != f.endDay')
            ->setParameter('trip', $trip)
            ->orderBy('sd.position', 'ASC')
            ->addOrderBy('f.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countFirstDayOverNightFlight(Trip $trip): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.id)')
            ->join('f.startDay', 'sd')
            ->where('sd.trip = :trip')
            ->andWhere('f.startDay != f.endDay')
            ->andWhere('sd.position = 1')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
