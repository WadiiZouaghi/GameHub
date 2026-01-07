<?php

namespace App\Repository;

use App\Entity\Wishlist;
use App\Entity\User;
use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function isInWishlist(User $user, Game $game): bool
    {
        return $this->count(['user' => $user, 'game' => $game]) > 0;
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWishlistItem(User $user, Game $game): ?Wishlist
    {
        return $this->findOneBy(['user' => $user, 'game' => $game]);
    }
}
