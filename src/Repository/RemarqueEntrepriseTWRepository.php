<?php

namespace App\Repository;

use App\Entity\RemarqueEntrepriseTW;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method RemarqueEntrepriseTW|null find($id, $lockMode = null, $lockVersion = null)
 * @method RemarqueEntrepriseTW|null findOneBy(array $criteria, array $orderBy = null)
 * @method RemarqueEntrepriseTW[]    findAll()
 * @method RemarqueEntrepriseTW[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemarqueEntrepriseTWRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemarqueEntrepriseTW::class);
    }

    // /**
    //  * @return RemarqueEntrepriseTW[] Returns an array of RemarqueEntrepriseTW objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RemarqueEntrepriseTW
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
