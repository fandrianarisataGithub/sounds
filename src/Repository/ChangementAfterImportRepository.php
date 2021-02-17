<?php

namespace App\Repository;

use App\Entity\ChangementAfterImport;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ChangementAfterImport|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangementAfterImport|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangementAfterImport[]    findAll()
 * @method ChangementAfterImport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangementAfterImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChangementAfterImport::class);
    }

    public function findChangeByInterval($interval, $id_pro)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                SELECT id_pro, nom, last_data, next_data FROM `changement_after_import` 
                LEFT JOIN `liste_pfupdated`
                ON changement_after_import.liste_pfupdated_id = liste_pfupdated.id
                INNER JOIN interval_change_pf as intervalle
                WHERE liste_pfupdated.id_pro = :id_pro 
                AND intervalle.intervalle = :interval
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'interval'  => $interval,
            'id_pro' => $id_pro
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    // /**
    //  * @return ChangementAfterImport[] Returns an array of ChangementAfterImport objects
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
    public function findOneBySomeField($value): ?ChangementAfterImport
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
