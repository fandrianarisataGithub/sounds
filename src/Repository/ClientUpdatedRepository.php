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

    public function findDistinctClientByInterval($interval){
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                SELECT DISTINCT nom FROM `client_updated` AS client 
                INNER JOIN `client_updated_interval_change_pf` AS intermediaire
                ON intermediaire.client_updated_id = client.id
                INNER JOIN interval_change_pf as intervalle WHERE intervalle.intervalle = :interval
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['interval' => $interval]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
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
