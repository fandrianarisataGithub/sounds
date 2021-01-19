<?php

namespace App\Repository;

use App\Entity\ContactEntrepriseTW;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactEntrepriseTW|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactEntrepriseTW|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactEntrepriseTW[]    findAll()
 * @method ContactEntrepriseTW[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactEntrepriseTWRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactEntrepriseTW::class);
    }

    // /**
    //  * @return ContactEntrepriseTW[] Returns an array of ContactEntrepriseTW objects
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
    public function findOneBySomeField($value): ?ContactEntrepriseTW
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
