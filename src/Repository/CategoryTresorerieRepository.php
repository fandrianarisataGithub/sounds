<?php

namespace App\Repository;

use App\Entity\CategoryTresorerie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryTresorerie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryTresorerie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryTresorerie[]    findAll()
 * @method CategoryTresorerie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryTresorerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryTresorerie::class);
    }

    // /**
    //  * @return CategoryTresorerie[] Returns an array of CategoryTresorerie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryTresorerie
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
