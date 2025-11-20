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
    public function findAccommodationsByTrip(Trip $trip): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.startDay', 'sd')
            ->where('sd.trip = :trip')
            ->setParameter('trip', $trip)
            ->orderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
