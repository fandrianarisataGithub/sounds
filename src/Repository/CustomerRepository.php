<?php

namespace App\Repository;

use DateTime;
use App\Entity\Customer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    
    public function findCustomersByVisit($hotel)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('App\Entity\Visit', 'v', 'WITH', 'c.id = v.customer')
            ->andWhere('v.hotel = :val')
            ->setParameter('val', $hotel)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findCustByData($tab)
    {
       // dd($tab);
        $by_email = !empty($tab['email']) ? $this->createQueryBuilder('c')
        ->andWhere('c.email = :val')
        ->setParameter('val', $tab['email'])
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult() : null;
        if($by_email){
            dd("mail" . $by_email);
            return $by_email;
        }
        $by_phone = !empty($tab['telephone']) ? $this->createQueryBuilder('c')
        ->andWhere('c.telephone = :val')
        ->setParameter('val', $tab['telephone'])
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult() : null;
        if($by_phone){
            return $by_phone;
        }
        
        $by_names = $this->createQueryBuilder('c')
            ->andWhere('c.name = :val1 AND c.name = :val2')
            ->setParameter('val1', $tab['name'])
            ->setParameter('val2', $tab['lastName'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if($by_names){
            return $by_names;
        }
        else if(!$by_email && !$by_phone && !$by_names){
           
            return null;
        }
        
    }

    public function findAllForVue()
    {
        return $this->createQueryBuilder('c')
            ->select('c.name, c.lastName, c.email, c.telephone, c.id')
            ->distinct()
            ->getQuery()
            ->getResult()
        ; 
    }

    public function findallCustAndhisFidelisation()
    {
        return $this->createQueryBuilder('c')
            ->innerJoin("App\Entity\Fidelisation", "f", "WITH", "c.fidelisation = f")
            ->addSelect('f.nom AS fid_nom, f.style_etiquette AS style_etiquette')
            ->getQuery()
            ->getResult()
        ; 
    }
   

    /*
    public function findOneBySomeField($value): ?Customer
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
