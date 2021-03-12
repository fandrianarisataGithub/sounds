<?php

namespace App\Repository;

use App\Entity\TresorerieRecette;
use App\Repository\TresorerieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    /**
     * @return TresorerieRecette[] Returns an array of TresorerieRecette objects
     */
    public function findRecettebetween($date1, $date2)
    {   
        
        $query = $this->createQueryBuilder('t');
        if($date1 && $date2){
           if($date1 < $date2){
            
                $query->andWhere('t.date <= :date2 AND t.date >= :date1')
                    ->setParameter('date1', $date1)
                    ->setParameter('date2', $date2)
                ;
           }
           else if($date1 > $date2){
                $query->andWhere('t.date <= :date1 AND t.date >= :date2')
                    ->setParameter('date1', $date1)
                    ->setParameter('date2', $date2)
                ;
           }
           else if($date1 == $date2){
                $query->andWhere('t.date = :date2')
                    ->setParameter('date2', $date2)
                ;
           }
        }
       
        return $query
        ->orderBy('t.id', 'DESC')
        ->getQuery()
        ->getResult();
       
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
