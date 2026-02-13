<?php

namespace App\Repository;

use App\Entity\Accommodation;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Accommodation>
 */
class AccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accommodation::class);
    }

    /** @return Accommodation[] */
    public function findAccommodationsByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('p')
            ->leftJoin('a.startDay', 'sd')
            ->leftJoin('a.place', 'p')
            ->where('sd.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAccommodationsByTrip(Trip $trip): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.trip = :trip')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
