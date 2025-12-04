<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function getReservationsTrendData(\DateTime $startDate): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('DATE(r.createdAt) as date, COUNT(r.id) as count')
            ->where('r.createdAt >= :startDate')
            ->setParameter('startDate', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery();

        $results = $qb->getResult();
        
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'date' => $row['date'],
                'count' => (int)$row['count']
            ];
        }
        
        return $data;
    }
}