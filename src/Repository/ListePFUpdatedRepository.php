<?php

namespace App\Repository;

use App\Entity\ListePFUpdated;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListePFUpdated|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListePFUpdated|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListePFUpdated[]    findAll()
 * @method ListePFUpdated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListePFUpdatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListePFUpdated::class);
    }

    // /**
    //  * @return ListePFUpdated[] Returns an array of ListePFUpdated objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ListePFUpdated
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
