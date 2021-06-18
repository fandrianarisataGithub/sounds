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
    
    public function findCustByData($tab): ?Customer
    {
        $by_email = $this->createQueryBuilder('c')
                ->andWhere('c.email = :val')
                ->setParameter('val', $tab['email'])
                ->getQuery()
                ->getOneOrNullResult();
        if($by_email){
            return $by_email;
        }
        $by_phone = $this->createQueryBuilder('c')
        ->andWhere('c.telephone = :val')
        ->setParameter('val', $tab['telephone'])
        ->getQuery()
        ->getOneOrNullResult();
        if($by_phone){
            return $by_phone;
        }
        
        $by_names = $this->createQueryBuilder('c')
            ->andWhere('c.name = :val1 AND c.name = :val2')
            ->setParameter('val1', $tab['name'])
            ->setParameter('val2', $tab['lastName'])
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
