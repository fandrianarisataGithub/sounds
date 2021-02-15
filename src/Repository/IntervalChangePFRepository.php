<?php

namespace App\Repository;

use App\Entity\ClientUpdated;
use App\Entity\ListePFUpdated;
use App\Entity\IntervalChangePF;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method IntervalChangePF|null find($id, $lockMode = null, $lockVersion = null)
 * @method IntervalChangePF|null findOneBy(array $criteria, array $orderBy = null)
 * @method IntervalChangePF[]    findAll()
 * @method IntervalChangePF[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntervalChangePFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntervalChangePF::class);
    }
    /**
     * @return IntervalChangePF[] Returns an array of IntervalChangePF objects
     */
    public function findAll()
    {
        return $this->createQueryBuilder('i')
           
            ->orderBy('i.id', 'DESC')
           
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?IntervalChangePF
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
