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

    public function findFlightsByTrip(\App\Entity\Trip $trip): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.startDay', 'sd')
            ->where('sd.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('sd.position', 'ASC')
            ->addOrderBy('f.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
