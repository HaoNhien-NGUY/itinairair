<?php

namespace App\Repository;

use App\Entity\Day;
use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
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
     * @return array<int, TravelItem[]>
     */
    public function findItemDayPairsForTrip(Trip $trip): array
    {
        /** @var array<int, array{itemId: int, dayId: int}> $scalarRows */
        $scalarRows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('i.id AS itemId', 'd.id AS dayId')
            ->from(TravelItem::class, 'i')
            ->join('i.startDay', 'sd')
            ->leftJoin('i.endDay', 'ed')
            ->join('sd.trip', 'tr')
            ->join(Day::class, 'd', 'WITH', 'd.trip = tr AND d.date BETWEEN sd.date AND COALESCE(ed.date, sd.date)')
            ->where('tr = :trip')
            ->andWhere('i.status IN (:statuses)')
            ->orderBy('d.position', 'ASC')
            ->addOrderBy('i.position', 'ASC')
            ->addOrderBy('sd.position', 'ASC')
            ->setParameter('trip', $trip)
            ->setParameter('statuses', ItemStatus::committed())
            ->getQuery()
            ->getScalarResult();

        if (!$scalarRows) {
            return [];
        }

        /** @var array<int, TravelItem> $itemsById */
        $itemsById = $this->createQueryBuilder('it', 'it.id')
            ->addSelect('p')
            ->leftJoin('it.place', 'p')
            ->where('it.id IN (:ids)')
            ->setParameter('ids', array_unique(array_map(static fn (array $r) => (int) $r['itemId'], $scalarRows)))
            ->getQuery()
            ->getResult();

        $out = [];

        foreach ($scalarRows as $row) {
            $dayId = $row['dayId'];
            $itemId = $row['itemId'];

            if (isset($itemsById[$itemId])) {
                $out[$dayId][] = $itemsById[$itemId];
            }
        }

        return $out;
    }

    /**
     * @param ItemStatus[]|null $statuses
     *
     * @return TravelItem[]
     */
    public function findItemsForDay(Day $day, ?array $statuses = null): array
    {
        return $this->createQueryBuilder('i')
            ->addSelect('sd', 'ed', 'p')
            ->join('i.startDay', 'sd')
            ->leftJoin('i.endDay', 'ed')
            ->leftJoin('i.place', 'p')
            ->where(':day_date BETWEEN sd.date AND COALESCE(ed.date, sd.date)')
            ->andWhere('sd.trip = :trip')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('day_date', $day->getDate())
            ->setParameter('trip', $day->getTrip())
            ->setParameter('statuses', $statuses ?? ItemStatus::committed())
            ->addOrderBy('i.position', 'ASC')
            ->addOrderBy('sd.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ItemStatus[]|null $statuses
     *
     * @return TravelItem[]
     */
    public function findItemsForTrip(Trip $trip, ?array $statuses = null): array
    {
        return $this->createQueryBuilder('i')
            ->addSelect('p')
            ->leftJoin('i.place', 'p')
            ->where('i.trip = :trip')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('statuses', $statuses ?? ItemStatus::committed())
            ->setParameter('trip', $trip)
            ->addOrderBy('i.position', 'ASC')
            ->getQuery()
            ->getResult();
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
