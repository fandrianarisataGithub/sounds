<?php

namespace App\Repository;

use App\Entity\DonneeDuJour;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DonneeDuJour|null find($id, $lockMode = null, $lockVersion = null)
 * @method DonneeDuJour|null findOneBy(array $criteria, array $orderBy = null)
 * @method DonneeDuJour[]    findAll()
 * @method DonneeDuJour[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonneeDuJourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DonneeDuJour::class);
    }
    public function findAll()
    {
        return $this->findBy(array(), array('createdAt' => 'DESC'));
    }

    // /**
    //  * @return DonneeDuJour[] Returns an array of DonneeDuJour objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DonneeDuJour
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
