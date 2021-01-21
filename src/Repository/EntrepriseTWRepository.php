<?php

namespace App\Repository;

use App\Entity\EntrepriseTW;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method EntrepriseTW|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntrepriseTW|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntrepriseTW[]    findAll()
 * @method EntrepriseTW[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntrepriseTWRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntrepriseTW::class);
    }

    public function findAllNomEntreprise(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DISTINCT nom FROM entreprise_tw 
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tab = $stmt->fetchAll();

        $liste = [];
        foreach($tab as $item){
            array_push($liste, trim($item['nom']));
        }
        // returns an array of arrays (i.e. a raw data set)
        return $liste;
    }
    // /**
    //  * @return EntrepriseTW[] Returns an array of EntrepriseTW objects
    //  */
    public function touslesNoms()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DISTINCT nom FROM entreprise_tw 
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $tab = $stmt->fetchAll();
        $tab_simple = [];
        foreach($tab as $value){
            
            array_push($tab_simple, $value["nom"]);
        }
        return $tab_simple;
    }
    

    // /**
    //  * @return EntrepriseTW[] Returns an array of EntrepriseTW objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EntrepriseTW
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
