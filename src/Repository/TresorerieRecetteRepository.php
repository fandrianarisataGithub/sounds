<?php

namespace App\Repository;

use App\Entity\TresorerieRecette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TresorerieRecette|null find($id, $lockMode = null, $lockVersion = null)
 * @method TresorerieRecette|null findOneBy(array $criteria, array $orderBy = null)
 * @method TresorerieRecette[]    findAll()
 * @method TresorerieRecette[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorerieRecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TresorerieRecette::class);
    }

    // /**
    //  * @return TresorerieRecette[] Returns an array of TresorerieRecette objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TresorerieRecette
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
