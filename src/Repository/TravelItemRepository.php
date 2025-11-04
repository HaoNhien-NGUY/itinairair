<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TravelItem>
 */
class TravelItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TravelItem::class);
    }

    /**
     * Returns TravelItems grouped by day within the given trip.
     * Format: [ <dayId> => [<TravelItem>, ...], ... ]
     */
    public function findItemDayPairsForTrip(Trip $trip): array
    {
        $scalarRows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('i.id AS itemId', 'd.id AS dayId')
            ->from(TravelItem::class, 'i')
            ->join('i.startDay', 'sd')
            ->leftJoin('i.endDay', 'ed')
            ->join('sd.trip', 'tr')
            ->join(Day::class, 'd', 'WITH', 'd.trip = tr AND d.position BETWEEN sd.position AND COALESCE(ed.position, sd.position)')
            ->where('tr = :trip')
            ->orderBy('d.position', 'ASC')
            ->addOrderBy('i.position', 'ASC')
            ->setParameter('trip', $trip)
            ->getQuery()
            ->getScalarResult();

        if (!$scalarRows) {
            return [];
        }

        $ids = array_values(array_unique(array_map(static fn(array $r) => (int) $r['itemId'], $scalarRows)));
        $itemsById = $this->createQueryBuilder('it', 'it.id')
            ->where('it.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($scalarRows as $row) {
            $out[$row['dayId']][] = $itemsById[$row['itemId']];;
        }

        return $out;
    }

    public function insertAtPosition(TravelItem $item, int $position): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(TravelItem::class, 'i')
            ->set('i.position', 'i.position + 1')
            ->where('i.position >= :position')
            ->andWhere('i.startDay = :day')
            ->setParameter('position', $position)
            ->setParameter('day', $item->getStartDay())
            ->getQuery()
            ->execute();
    }
}
