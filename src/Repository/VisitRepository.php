<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    /**
     * @return Visit[] Returns an array of Visit objects
     */
    public function findCustomersByVisit($hotel)
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('App\Entity\Customer', 'c', 'WITH', 'c.id = v.customer')
            ->addSelect('c.email', 'c.telephone', 'c.name', 'c.lastName')
            ->andWhere('v.hotel = :val')
            ->setParameter('val', $hotel)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    // dernier date de visit pour un client id
    public function findLastDateVisit($customer) 
    {
       return $this->createQueryBuilder('v')
            ->andWhere('v.customer = :val')
            ->setParameter('val', $customer)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getEffectifByProvenance(){
        
        $effectif = [
            ['nom' => 'OTA', 'eff' => 0],
            ['nom' => 'TOA', 'eff' => 0],
            ['nom' => 'CORPO', 'eff' => 0],
            ['nom' => 'DIRECT', 'eff' => 0]
        ];
        
        $data = $this->createQueryBuilder('v')
            ->select('v.provenance, COUNT(v.provenance) AS effectif')
            ->innerJoin('App\Entity\Customer' , 'c',  'WITH' , 'v.customer = c')
            ->innerJoin('App\Entity\Fidelisation' , 'f',  'WITH' , 'c.fidelisation = f')
            ->groupBy('v.provenance')
            ->getQuery()
            ->getResult()
        ;
        //dd($data);
        foreach($data as $d){
            if($d['provenance'] == 'OTA'){
                $effectif[0]['eff'] = $d['effectif'];
            }
            if($d['provenance'] == 'TOA'){
                $effectif[1]['eff'] = $d['effectif'];
            }
            if($d['provenance'] == 'CORPO'){
                $effectif[2]['eff'] = $d['effectif'];
            }
            if($d['provenance'] == 'DIRECT'){
                $effectif[3]['eff'] = $d['effectif'];
            }
        }

        return $effectif;
    }

    public function findallCustAndhisFidelisationAndTypeVisit(string $type)
    {
        return $this->createQueryBuilder('v')
            ->innerJoin('App\Entity\Customer' , 'c',  'WITH' , 'v.customer = c')
            ->innerJoin('App\Entity\Fidelisation' , 'f',  'WITH' , 'c.fidelisation = f')
            ->andWhere('v.provenance = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult()
        ; 
    }

    public function getEffectifByProvOTABySource()
    {   
       
        $effectif = [
            ['nom' => 'BOOKING', 'eff' => 0, 'ca' => 0],
            ['nom' => 'EXPEDIA', 'eff' => 0, 'ca' => 0],
            ['nom' => 'HOTELBEDS', 'eff' => 0, 'ca' => 0]
        ];
        
        $data = $this->createQueryBuilder('v')
            ->innerJoin('App\Entity\Customer' , 'c',  'WITH' , 'v.customer = c')
            ->innerJoin('App\Entity\Fidelisation' , 'f',  'WITH' , 'c.fidelisation = f')
            ->select('v.source, COUNT(v.source) AS effectif, SUM(v.montant) AS ca')
            ->andWhere('v.provenance = :prov')
            ->setParameter('prov', 'OTA')
            ->groupBy('v.source')
            ->getQuery()
            ->getResult()
        ;
        
        foreach($data as $d){
            if($d['source'] == 'Booking'){
                $effectif[0]['eff'] = $d['effectif'];
                $effectif[0]['ca'] = $d['ca'];
            }
            if($d['source'] == 'Expedia'){
                $effectif[1]['eff'] = $d['effectif'];
                $effectif[1]['ca'] = $d['ca'];
            }
            if($d['source'] == 'Hotelbeds'){
                $effectif[2]['eff'] = $d['effectif'];
                $effectif[2]['ca'] = $d['ca'];
            }
        }
        //dd($effectif);
        return $effectif;
    }

    public function getEffectifByProvDirectBySource()
    {   
        /*
            var chartData = [
                {"nom": "EMAIL", "eff": 20, "ca" : 12000000}, 
                {"nom": "TELEPHONE", "eff" : 0 , "ca": 2025000}, 
                {"nom": "SITE WEB", "eff" : 10 ,"ca": 13000000}, 
                {"nom": "WALKING","eff" : 30 , "ca": 14000000}
            ] 
         */
       
        $effectif = [
            ['nom' => 'EMAIL', 'eff' => 0, 'ca' => 0],
            ['nom' => 'TELEPHONE', 'eff' => 0, 'ca' => 0],
            ['nom' => 'SITE WEB', 'eff' => 0, 'ca' => 0],
            ['nom' => 'WALKING', 'eff' => 0, 'ca' => 0]
        ];
        
        $data = $this->createQueryBuilder('v')
            ->innerJoin('App\Entity\Customer' , 'c',  'WITH' , 'v.customer = c')
            ->innerJoin('App\Entity\Fidelisation' , 'f',  'WITH' , 'c.fidelisation = f')
            ->select('v.source, COUNT(v.source) AS effectif, SUM(v.montant) AS ca')
            ->andWhere('v.provenance = :prov')
            ->setParameter('prov', 'DIRECT')
            ->groupBy('v.source')
            ->getQuery()
            ->getResult()
        ;
        
        foreach($data as $d){
            if($d['source'] == 'Email'){
                $effectif[0]['eff'] = $d['effectif'];
                $effectif[0]['ca'] = $d['ca'];
            }
            if($d['source'] == 'Téléphone'){
                $effectif[1]['eff'] = $d['effectif'];
                $effectif[1]['ca'] = $d['ca'];
            }
            if($d['source'] == 'Site web'){
                $effectif[2]['eff'] = $d['effectif'];
                $effectif[2]['ca'] = $d['ca'];
            }
            if($d['source'] == 'Comptoir'){
                $effectif[3]['eff'] = $d['effectif'];
                $effectif[3]['ca'] = $d['ca'];
            }
        }
        //dd($effectif);
        return $effectif;
    }

    // /**
    //  * @return Visit[] Returns an array of Visit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Visit
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
