<?php

namespace App\Repository;

use App\Entity\FicheHotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FicheHotel|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheHotel|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheHotel[]    findAll()
 * @method FicheHotel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheHotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheHotel::class);
    }

    // /**
    //  * @return FicheHotel[] Returns an array of FicheHotel objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FicheHotel
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
