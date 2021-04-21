<?php

namespace App\Repository;

use App\Entity\SousCategorieTresorerie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SousCategorieTresorerie|null find($id, $lockMode = null, $lockVersion = null)
 * @method SousCategorieTresorerie|null findOneBy(array $criteria, array $orderBy = null)
 * @method SousCategorieTresorerie[]    findAll()
 * @method SousCategorieTresorerie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SousCategorieTresorerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SousCategorieTresorerie::class);
    }

    // /**
    //  * @return SousCategorieTresorerie[] Returns an array of SousCategorieTresorerie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SousCategorieTresorerie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
