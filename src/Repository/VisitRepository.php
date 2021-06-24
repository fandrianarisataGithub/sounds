<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    /**
     * @return Visit[] Returns an array of Visit objects
     */
    public function findCustomersByVisit($hotel)
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('App\Entity\Customer', 'c', 'WITH', 'c.id = v.customer')
            ->addSelect('c.email', 'c.telephone', 'c.name', 'c.lastName')
            ->andWhere('v.hotel = :val')
            ->setParameter('val', $hotel)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    // dernier date de visit pour un client id
    public function findLastDateVisit($customer) 
    {
       return $this->createQueryBuilder('v')
            ->andWhere('v.customer = :val')
            ->setParameter('val', $customer)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Visit[] Returns an array of Visit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Visit
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
