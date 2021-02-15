<?php

namespace App\Repository;

use App\Entity\ClientUpdated;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ClientUpdated|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientUpdated|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientUpdated[]    findAll()
 * @method ClientUpdated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientUpdatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientUpdated::class);
    }

    // /**
    //  * @return ClientUpdated[] Returns an array of ClientUpdated objects
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
    /**
     * @return ClientUpdated[] Returns an array of ClientUpdated objects
     */
    public function findClientByInterval($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.intervalchangePF = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?ClientUpdated
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
