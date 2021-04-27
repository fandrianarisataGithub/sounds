<?php

namespace App\Repository;

use App\Entity\Tresorerie;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Tresorerie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tresorerie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tresorerie[]    findAll()
 * @method Tresorerie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TresorerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tresorerie::class);
    }

    /**
     * @return Tresorerie[] Returns an array of Tresorerie objects
     */
    public function find_between($date1, $date2, $key_flux)
    {
        $query = $this->createQueryBuilder('t');
        if($key_flux == "encaissement" || $key_flux == "decaissement"){
            $query->andWhere('t.type_flux = :type_flux')
            ->setParameter('type_flux', $key_flux);
        }
        if(($date1 != null && $date2 == null) || ($date1 == null && $date2 != null)){
            $query->andWhere('t.date_paiment = :date1 OR t.date_paiment = :date2')
            ->setParameter('date1', $date1)
            ->setParameter('date2', $date2);
        }
        else if($date1 != null && $date2 != null){
            $query->andWhere('t.date_paiment BETWEEN :date1 AND :date2')
            ->setParameter('date1', $date1)
            ->setParameter('date2', $date2);
        }
        return $query
            ->getQuery()
            ->getResult();
    }
    
    /**
     * @return Tresorerie[] Returns an array of Tresorerie objects
    */
    public function findLikeIdPro($val)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.idPro LIKE :val')
            ->setParameter('val', '%'.$val.'%')
            ->getQuery()
            ->getResult();
        ;
    }

    /**
     * @return Tresorerie[] Returns an array of Tresorerie objects
    */
    public function findLikeSage($val)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.num_sage LIKE :val')
            ->setParameter('val', '%'.$val.'%')
            ->getQuery()
            ->getResult();
        ;
    }

    /**
     * @return Tresorerie[] Returns an array of Tresorerie objects
    */
    public function findLikePrestataire($val)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.prestataire LIKE :val')
            ->setParameter('val', '%'.$val.'%')
            ->getQuery()
            ->getResult();
        ;
    }

    /**
     * @return Tresorerie[] Returns an array of Tresorerie objects
    */
    public function findLikeClient($val)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.client LIKE :val')
            ->setParameter('val', '%'.$val.'%')
            ->getQuery()
            ->getResult();
        ;
    }

    /*
    public function findOneBySomeField($value): ?Tresorerie
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
