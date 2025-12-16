<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\Destination;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        return $this->createQueryBuilder('d')
            ->where('d.trip = :trip')
            ->setParameter('trip', $trip)
            ->join('d.startDay', 'sd')
            ->orderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
