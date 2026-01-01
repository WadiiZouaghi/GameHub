<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function getNewGamesTrendData(\DateTime $startDate): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT DATE(created_at) as date, COUNT(id) as count
            FROM game
            WHERE created_at >= :startDate
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['startDate' => $startDate->format('Y-m-d H:i:s')]);

        $data = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $data[] = [
                'date' => $row['date'],
                'count' => (int)$row['count']
            ];
        }

        return $data;
    }

//    /**
//     * @return Game[] Returns an array of Game objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Game
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
