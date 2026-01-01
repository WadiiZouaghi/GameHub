<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getNewUsersTrendData(\DateTime $startDate): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT DATE(created_at) as date, COUNT(id) as count
            FROM user
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

    public function findRecentUsers(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
