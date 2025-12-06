<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Enum\TravelItemType;
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
            ->andWhere('i.status IN (:statuses)')
            ->orderBy('d.position', 'ASC')
            ->addOrderBy('i.position', 'ASC')
            ->setParameter('trip', $trip)
            ->setParameter('statuses', ItemStatus::committed())
            ->getQuery()
            ->getScalarResult();

        if (!$scalarRows) {
            return [];
        }

        $ids = array_values(array_unique(array_map(static fn(array $r) => (int) $r['itemId'], $scalarRows)));
        $itemsById = $this->createQueryBuilder('it', 'it.id')
            ->addSelect('p')
            ->leftJoin('it.place', 'p')
            ->where('it.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($scalarRows as $row) {
            $dayId = $row['dayId'];
            /** @var TravelItem $item */
            $item = $itemsById[$row['itemId']];

            $itemType = $item->getItemType();
            $out[$dayId][$itemType->isPositionable() ? 'itinerary' : $itemType->value][] = $item;
        }

        return $out;
    }

    /**
     * Retourne les TravelItems pour un jour spécifique, groupés par type.
     * Format: [ 'itinerary' => [...], 'accommodation' => [...], 'flight' => [...] ]
     */
    public function findItemsForDay(Day $day): array
    {
        // On récupère directement les entités TravelItem qui correspondent à notre critère
        $items = $this->createQueryBuilder('i')
            ->addSelect('sd', 'ed', 'p')
            ->join('i.startDay', 'sd')
            ->leftJoin('i.endDay', 'ed')
            ->leftJoin('i.place', 'p')
            ->where(':day_position BETWEEN sd.position AND COALESCE(ed.position, sd.position)')
            ->andWhere('sd.trip = :trip')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('day_position', $day->getPosition())
            ->setParameter('trip', $day->getTrip())
            ->setParameter('statuses', ItemStatus::committed())
//            ->addOrderBy(
//                'CASE ' .
//                'WHEN sd.position < :day_position AND COALESCE(ed.position, sd.position) > :day_position THEN 0 ' . // All-day
//                'WHEN COALESCE(ed.position, sd.position) = :day_position AND sd.position < :day_position THEN 1 ' . // Ending
//                'WHEN sd.position = :day_position AND COALESCE(ed.position, sd.position) > :day_position THEN 3 ' . // Starting
//                'ELSE 2 ' . // one-day
//                'END'
//            )
            ->addOrderBy('i.position', 'ASC')
            ->addOrderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($items)) {
            return [];
        }

        $groupedItems = [];
        foreach ($items as $item) {
            /** @var TravelItemType $itemType */
            $itemType = $item->getItemType();
            $groupedItems[$itemType->isPositionable() ? 'itinerary' : $itemType->value][] = $item;
        }

        return $groupedItems;
    }

    public function makeSpaceAtPosition(Day $day, int $position): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->update(TravelItem::class, 'i')
            ->set('i.position', 'i.position + 1')
            ->where('i.position >= :position')
            ->andWhere('i.startDay = :day')
            ->andWhere('i.position is not null')
            ->setParameter('position', $position)
            ->setParameter('day', $day)
            ->getQuery()
            ->execute();
    }
}
