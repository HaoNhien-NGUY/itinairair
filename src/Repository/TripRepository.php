<?php

namespace App\Repository;

use App\Entity\Destination;
use App\Entity\TravelItem;
use App\Entity\Trip;
use DateTime;
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
         * @return array{ongoing: Trip[], coming: Trip[], past: Trip[]}
         */
        public function findByUser(UserInterface $user): array
        {
            $trips = $this->createQueryBuilder('t')
                ->addSelect('tm')
                ->join('t.tripMemberships', 'tm')
                ->where('tm.member = :user')
                ->setParameter('user', $user)
                ->orderBy('t.startDate', 'ASC')
                ->getQuery()
                ->getResult();

            $results = [
                'ongoing' => [],
                'coming'  => [],
                'past'    => [],
                'ids'     => [],
            ];

            $now = new DateTime();

            foreach ($trips as $trip) {
                $results['ids'][] = $trip->getId();

                if ($trip->getStartDate() > $now) {
                    $results['coming'][] = $trip;
                } elseif ($trip->getEndDate() < $now) {
                    $results['past'][] = $trip;
                } else {
                    $results['ongoing'][] = $trip;
                }
            }

            usort($results['past'], function($a, $b) {
                return $b->getEndDate() <=> $a->getEndDate();
            });

            return $results;
        }
}
