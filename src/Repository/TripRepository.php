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
     * @return array{ongoing: Trip[], coming: Trip[], past: Trip[], ids: int[], count: int}
     */
    public function findByUser(UserInterface $user): array
    {
        $trips = $this->createQueryBuilder('t')
            ->addSelect('tm')
            ->innerJoin('t.tripMemberships', 'm')
            ->leftJoin('t.tripMemberships', 'tm')
            ->where('m.member = :user')
            ->setParameter('user', $user)
            ->orderBy('t.startDate', 'ASC')
            ->getQuery()
            ->getResult();

        $results = [
            'ongoing' => [],
            'coming'  => [],
            'past'    => [],
            'ids'     => [],
            'count'   => 0,
        ];

        $now = new \DateTime();

        /** @var Trip $trip */
        foreach ($trips as $trip) {
            $results['ids'][] = $trip->getId();
            ++$results['count'];

            if ($trip->getStartDate() > $now) {
                $results['coming'][] = $trip;
            } elseif ($trip->getEndDate() < $now) {
                $results['past'][] = $trip;
            } else {
                $results['ongoing'][] = $trip;
            }
        }

        usort($results['past'], function ($a, $b) {
            return $b->getEndDate() <=> $a->getEndDate();
        });

        return $results;
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
