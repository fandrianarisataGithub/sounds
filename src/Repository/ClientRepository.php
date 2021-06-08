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
                ->getQuery()
                ->getResult()
        ;
    }

    public function findAllForVue()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT DISTINCT c.nom, c.email, c.telephone, c.prenom
            FROM App\Entity\Client c
            WHERE c.email is not NULL OR c.telephone is not NULL'
        );

        // returns an array of Product objects
        return $query->getResult();
       
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
