<?php

namespace App\Repository;

use App\Entity\ClientUpload;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ClientUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientUpload[]    findAll()
 * @method ClientUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientUploadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientUpload::class);
    }
    public function findAll()
    {
        return $this->findBy(array(), array('date' => 'DESC'));
    }

    // /**
    //  * @return ClientUpload[] Returns an array of ClientUpload objects
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
    public function findOneBySomeField($value): ?ClientUpload
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
