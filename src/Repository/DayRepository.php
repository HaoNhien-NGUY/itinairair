<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Day>
 */
class DayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Day::class);
    }

    public function findCurrentDayForTrip(Trip $trip): ?Day
    {
        $startDate = new \DateTime('today midnight');
        $endDate   = new \DateTime('today 23:59:59');

        return $this->createQueryBuilder('d')
            ->andWhere('d.date BETWEEN :start AND :end')
            ->andwhere('d.trip = :trip')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('trip', $trip)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
