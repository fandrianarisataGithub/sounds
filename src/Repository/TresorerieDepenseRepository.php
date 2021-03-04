<?php

namespace App\Repository;

use App\Entity\TresorerieDepense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TresorerieDepense|null find($id, $lockMode = null, $lockVersion = null)
 * @method TresorerieDepense|null findOneBy(array $criteria, array $orderBy = null)
 * @method TresorerieDepense[]    findAll()
 * @method TresorerieDepense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorerieDepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TresorerieDepense::class);
    }

    // /**
    //  * @return TresorerieDepense[] Returns an array of TresorerieDepense objects
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
    public function findOneBySomeField($value): ?TresorerieDepense
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
