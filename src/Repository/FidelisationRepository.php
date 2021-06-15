<?php

namespace App\Repository;

use App\Entity\Fidelisation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Fidelisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fidelisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fidelisation[]    findAll()
 * @method Fidelisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FidelisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fidelisation::class);
    }

    public function findAllClientsFid()
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->leftJoin('App\Entity\Client', 'c', 'WITH', 'f.id = c.fidelisation')
            ->addSelect('c')
        ;
        
        return $qb
            ->getQuery()
            ->getResult()
        ;

    }

    // /**
    //  * @return Fidelisation[] Returns an array of Fidelisation objects
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
    public function findOneBySomeField($value): ?Fidelisation
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
