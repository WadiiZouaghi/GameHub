<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function findLatest(int $limit = 10)
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.publishDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category, int $limit = 10)
    {
        return $this->createQueryBuilder('n')
            ->where('n.category = :category')
            ->setParameter('category', $category)
            ->orderBy('n.publishDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function search(string $query)
    {
        return $this->createQueryBuilder('n')
            ->where('LOWER(n.title) LIKE :query OR LOWER(n.content) LIKE :query')
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->orderBy('n.publishDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function incrementViewCount(News $news)
    {
        $news->setViewCount(($news->getViewCount() ?? 0) + 1);
        $this->getEntityManager()->flush();
    }
}
