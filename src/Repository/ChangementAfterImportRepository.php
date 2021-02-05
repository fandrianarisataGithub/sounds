<?php

namespace App\Repository;

use App\Entity\ChangementAfterImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChangementAfterImport|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangementAfterImport|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangementAfterImport[]    findAll()
 * @method ChangementAfterImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangementAfterImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangementAfterImport::class);
    }

    // /**
    //  * @return ChangementAfterImport[] Returns an array of ChangementAfterImport objects
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
    public function findOneBySomeField($value): ?ChangementAfterImport
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
