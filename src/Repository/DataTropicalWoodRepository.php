<?php

namespace App\Repository;

use App\Entity\DataTropicalWood;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DataTropicalWood|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataTropicalWood|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataTropicalWood[]    findAll()
 * @method DataTropicalWood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataTropicalWoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataTropicalWood::class);
    }
     /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function searchEntrepriseContact(string $value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.entreprise LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function searchDetail(string $value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.detail LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function filtrer($date1, $date2, array $type_transaction, array $etat_production, array $etat_paiement)
    {   
       
        if($date1 != "" && $date2 != ""){
            $t = count($etat_paiement);

            if ($t == 1) {
                // tsisy zany
                if(count($type_transaction)>1){
                    if(count($etat_production)>1){
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                    }
                    else{
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                    }
                }
                else{
                    if (count($etat_production) > 1) {
                        return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->getQuery()
                            ->getResult();
                    } else {
                        return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->getQuery()
                            ->getResult();
                    }
                }
            } else if ($t == 2) {
                // on enlève le premier element
                if (in_array("Aucun paiement", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab1)')
                            ->setParameter('tab1', $etat_production)
                            ->andWhere('d.total_reglement = 0')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if (count($type_transaction) > 1) {
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                        } else {
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                        }
                    }
                } 
                
                else if (in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                        }
                    }
                    else{
                        if (count($type_transaction) > 1) {
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                        } else {
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                        }
                    }
                } else if (in_array("Paiement total", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                }
            } else if ($t == 3) {
                if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement partiel", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                } else if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                } else if (in_array("Paiement partiel", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                }
            } else if ($t == 4) {
                if(count($etat_production)>1){
                    if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();
                    }
                    else{
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();
                    }
                }
                else{
                    if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();
                    }
                    else{
                        return $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();
                    }
                }
            }
        }
        else{
            $t = count($etat_paiement);
            if ($t == 1) {
                if(count($etat_production)>1){
                    if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                    }
                    else{
                        
                        return $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)
                        ->getQuery()
                        ->getResult();
                    }
                }
                else{
                    if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                        ->andWhere('d.type_transaction IN(:tab1)')
                        
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                    }
                    else{
                        return $this->createQueryBuilder('d')
                        ->getQuery()
                        ->getResult();
                    }
                }
            } else if ($t == 2) {
                // on enlève le premier element
                if (in_array("Aucun paiement", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                } else if (in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                        
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                } else if (in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                }
            } else if ($t == 3) {
                if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                } else if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                        else{
                            return $this->createQueryBuilder('d')
                           
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                        }
                    }
                } else if (in_array("Paiement partiel", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total >= d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                
                                ->andWhere('d.montant_total >= d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            return $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total >= d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                       else{
                            return $this->createQueryBuilder('d')
                                
                                ->andWhere('d.montant_total >= d.total_reglement')
                                ->getQuery()
                                ->getResult();
                       }
                   }
                }
            } else if ($t == 4) {
               if(count($etat_production)>1){
                   if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                   }else{
                        return $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                   }
               }
               else{
                   if(count($type_transaction)>1){
                        return $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                           
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                   }
                   else{
                        return $this->createQueryBuilder('d')
                           
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                   }
               }
            }
        }
       
    }



    // /**
    //  * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataTropicalWood
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
