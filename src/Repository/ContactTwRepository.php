<?php

namespace App\Repository;

use App\Entity\ContactTw;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactTw|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactTw|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactTw[]    findAll()
 * @method ContactTw[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactTwRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactTw::class);
    }

    // /**
    //  * @return ContactTw[] Returns an array of ContactTw objects
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
    public function findOneBySomeField($value): ?ContactTw
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
