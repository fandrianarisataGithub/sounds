<?php

namespace App\Repository;

use App\Entity\ListePFUpdated;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ListePFUpdated|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListePFUpdated|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListePFUpdated[]    findAll()
 * @method ListePFUpdated[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListePFUpdatedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListePFUpdated::class);
    }

    /**
     * @return ListePFUpdated[] Returns an array of ListePFUpdated objects
     */
    public function allChangeByPf($value)
    {
        return $this->createQueryBuilder('l')
            ->select('l')
            ->innerJoin('l.changementAfterImports', 'c',
                'WITH', 'c.listePFUpdated = :listePFUpdated')
            ->setParameter('listePFUpdated', $value)
            ->orderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDistinctListePFUpByInterval($interval, $nomClient)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                SELECT DISTINCT id_pro FROM `liste_pfupdated` LEFT JOIN client_updated 
                ON liste_pfupdated.client_updated_id = client_updated.id 
                INNER JOIN interval_change_pf as intervalle 
                WHERE intervalle.intervalle = :interval
                AND client_updated.nom = :nomClient
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute([
                        'interval'  => $interval,
                        'nomClient' => $nomClient
                        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }


    /*
    public function findOneBySomeField($value): ?ListePFUpdated
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
