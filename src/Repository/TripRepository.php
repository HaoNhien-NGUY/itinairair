<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Enum\TripRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Trip>
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    /**
     * @return Trip[]
     */
    public function findByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('t')
            ->addSelect('tm')
            ->innerJoin('t.tripMemberships', 'm')
            ->leftJoin('t.tripMemberships', 'tm')
            ->where('m.member = :user')
            ->setParameter('user', $user)
            ->orderBy('t.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Trip[]
     */
    public function findExpiredDemoTrips(\DateTimeImmutable $limitDate): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.tripMemberships', 'tm')
            ->join('tm.member', 'u')
            ->where('u.expiresAt < :now')
            ->andWhere('u.expiresAt IS NOT NULL')
            ->andWhere('t.isTemporary = true')
            ->andWhere('tm.role = :tm_role')
            ->setParameter('now', $limitDate)
            ->setParameter('tm_role', TripRole::ADMIN)
            ->getQuery()
            ->getResult();
    }
}
