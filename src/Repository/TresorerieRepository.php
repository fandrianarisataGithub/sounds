<?php

namespace App\Repository;

use App\Entity\Tresorerie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tresorerie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tresorerie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tresorerie[]    findAll()
 * @method Tresorerie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tresorerie::class);
    }

    // /**
    //  * @return Tresorerie[] Returns an array of Tresorerie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tresorerie
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
