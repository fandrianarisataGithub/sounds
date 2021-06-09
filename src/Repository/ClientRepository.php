<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\Client;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }
    public function findAll()
    {
        return $this->findBy(array(), array('createdAt' => 'DESC'));
    }
    //

    public function findClientBetweenTwoDates($hotel, $date1, $date2)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.hotel = :hotel')
            ->andWhere('c.dateArrivee >= :date1 AND c.dateDepart <= :date2')
            ->setParameter('hotel' , $hotel)
            ->setParameter('date1', $date1)
            ->setParameter('date2', $date2)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllClientsFid()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.fidelisation IS NOT NULL')
            ->getQuery()
            ->getResult()
        ;
        // $conn = $this->getEntityManager()
        //     ->getConnection();
        // $sql = 'SELECT * FROM client INNER JOIN fidelisation ON client.fidelisation_id = fidelisation.id';
        // $stmt = $conn->prepare($sql);
        // $stmt->execute();
        // return $stmt->fetchAll();
    }

    public function selectDistinc($nom)
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom, c.prenom, c.email, c.telephone')
            ->where('c.nom LIKE :word')
            ->setParameter('word', $nom.'%')
            ->distinct()
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllForVue()
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom, c.prenom, c.email, c.telephone, c.id')
            ->distinct()
            ->getQuery()
            ->getResult()
        ; 
    }

    public function searchTabIdentifiant($tab, \DateTime $start)
    {

        if($tab["mail"]){
            return $this->createQueryBuilder('c')
                ->andWhere('c.email = :val')
                ->andWhere('c.createdAt >= :start')
                ->setParameter('start', $start)
                ->setParameter('val', $tab["mail"])
                ->orderBy('c.createdAt', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
        
        if($tab["telephone"]){
            return $this->createQueryBuilder('c')
                ->andWhere('c.telephone = :val')
                ->andWhere('c.createdAt >= :start')
                ->setParameter('start', $start)
                ->setParameter('val', $tab["telephone"])
                ->orderBy('c.createdAt', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
        
    }
    
}
