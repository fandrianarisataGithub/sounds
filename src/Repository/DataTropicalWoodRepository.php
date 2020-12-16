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
    public function searchEntrepriseContact(array $liste)
    {   $Liste = [];
        for($i = 1 ; $i< count($liste); $i++){
            $liste_item = [];
            $liste1 = $this->createQueryBuilder('d')
            ->andWhere('d.entreprise LIKE :val')
            ->setParameter('val', '%' . $liste[$i] . '%')
            ->getQuery()
            ->getResult();
            
            $liste2 = $this->createQueryBuilder('d')
            ->andWhere('d.entreprise LIKE :val')
            ->setParameter('val', '%' . $liste[$i] . '%')
            ->addSelect('d.entreprise')
            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
            ->groupBy('d.entreprise')
            ->getQuery()
            ->getResult();
            $liste_item["entreprise"] = $liste2[0]["entreprise"];
            $liste_item["listes"] = $liste1;
            $liste_item["sous_total_montant_total"] = $liste2[0]["sous_total_montant_total"];
            $liste_item["sous_total_total_reglement"] = $liste2[0]["sous_total_total_reglement"];
            $liste_item["total_reste"] = $liste2[0]["total_reste"];
            
            array_push($Liste, $liste_item);
            
        }
       
        return $Liste;
       
    }

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function findAllGroupedAsc()
    {
        return $this->createQueryBuilder('d')
            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
            ->groupBy('d.entreprise')
            ->orderBy('sous_total_montant_total', 'ASC')
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
    public function findAllGroupedByEntreprise()
    {
        return $this->createQueryBuilder('d')
            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
            ->groupBy('d.entreprise')
            ->orderBy('sous_total_montant_total', 'ASC')
            ->getQuery()
            ->getResult();
    }
    

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function filtrer(  
            $date1, 
            $date2, 
            array $type_transaction, 
            array $etat_production, 
            array $etat_paiement,
            $typeReglement,
            $typeReste,
            $typeMontant
        )
    {   
       
        if($date1 != "" && $date2 != ""){
            $t = count($etat_paiement);
            if ($t == 1) {
                // tsisy zany
                if(count($type_transaction)>1){
                    if(count($etat_production)>1){
                        $liste1 =  $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->getQuery()
                                    ->getResult();

                        $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                                if ($typeReglement != null) {
                                    if ($typeReglement == "ASC") {
                                        $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                        ->getQuery()
                                            ->getResult();
                                    } else {
                                        $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                        ->getQuery()
                                            ->getResult();
                                    }
                                }
                                if ($typeReste != null) {
                                    if ($typeReste == "ASC") {
                                        $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                            ->getQuery()
                                            ->getResult();
                                    } else {
                                        $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                            ->getQuery()
                                            ->getResult();
                                    }
                                }
                                if ($typeMontant != null) {
                                    if ($typeMontant == "ASC") {
                                        $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                        ->getQuery()
                                            ->getResult();
                                    } else {
                                        $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                        ->getQuery()
                                            ->getResult();
                                    }
                                }

                                $Liste = [];
                                foreach ($liste2  as $l2) {
                                    $liste_item = [];
                                    $ligne_entreprise = [];
                                    $son_entreprise = $l2[0]->getEntreprise();
                                    foreach ($liste1 as $l1) {
                                        $entreprise_l1 = $l1->getEntreprise();
                                        if ($entreprise_l1 == $son_entreprise) {
                                            array_push($ligne_entreprise, $l1);
                                        }
                                    }
                                    $liste_item["entreprise"] = $son_entreprise;
                                    $liste_item["listes"] = $ligne_entreprise;
                                    $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                    $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                    $liste_item["total_reste"] = $l2["total_reste"];

                                    array_push($Liste, $liste_item);
                                }
                                return $Liste;
                    }
                    else{
                        $liste1 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                        
                        $liste2 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)

                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                        ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                        ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                        ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
                else{
                    if (count($etat_production) > 1) {
                        $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->getQuery()
                            ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    } else {
                        $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->getQuery()
                            ->getResult();
                        
                        $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
            } else if ($t == 2) {
                // on enlève le premier element
                if (in_array("Aucun paiement", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab1)')
                            ->setParameter('tab1', $etat_production)
                            ->andWhere('d.total_reglement = 0')
                            ->getQuery()
                            ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab1)')
                            ->setParameter('tab1', $etat_production)
                            ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if (count($type_transaction) > 1) {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                            
                                $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;

                        } else {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } 
                
                else if (in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
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

                        $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if (count($type_transaction) > 1) {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                            
                        } else {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total > d.total_reglement')
                                ->andHaving('d.total_reglement > 0')
                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } else if (in_array("Paiement total", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total = d.total_reglement')
                                
                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total = d.total_reglement')
                                
                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total = d.total_reglement')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                }
            } else if ($t == 3) {
                if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement partiel", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                
                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                
                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                ->getQuery()
                                ->getResult();

                            $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->setParameter('date1', $date1)
                                ->setParameter('date2', $date2)
                                ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                } else if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } else if (in_array("Paiement partiel", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 =  $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.montant_total >= d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                            ->setParameter('date1', $date1)
                            ->setParameter('date2', $date2)
                            ->andWhere('d.montant_total >= d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                }
            } else if ($t == 4) {
                if(count($etat_production)>1){
                    if(count($type_transaction)>1){
                        $liste1 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                    else{
                        $liste1 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)
                        ->andWhere('d.montant_total >= 0')
                        
                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
                else{
                    if(count($type_transaction)>1){
                        $liste1 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->andWhere('d.montant_total >= 0')
                        
                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                    else{
                        $liste1 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.montant_total >= 0')
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                        ->setParameter('date1', $date1)
                        ->setParameter('date2', $date2)
                        ->andWhere('d.montant_total >= 0')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
            }
        }
        else{
            $t = count($etat_paiement);
            if ($t == 1) {
                if(count($etat_production)>1){
                    if(count($type_transaction)>1){
                        $liste1 =  $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();
                        //dd($liste1);
                        
                        $liste2 = $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                        ->setParameter('tab2', $etat_production)
                        ->setParameter('tab1', $type_transaction)
                        
                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                        ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                        ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                        ->groupBy('d.entreprise');
                        if($typeReglement != null){
                           if($typeReglement == "ASC"){
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                           }else{
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                           }
                        }
                        if ($typeReste != null) {
                           if($typeReste == "ASC"){
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                           }else{
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                           }
                        }
                        if ($typeMontant != null) {
                           if($typeMontant == "ASC"){
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                           }else{
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                           }
                        }
                       
                        $Liste = [];
                        foreach($liste2  as $l2){
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach($liste1 as $l1){
                                $entreprise_l1 = $l1->getEntreprise();
                                if($entreprise_l1 == $son_entreprise){
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                       return $Liste;
                    }
                    else{
                        
                        $liste1 = $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->andWhere('d.etat_production IN(:tab2)')
                        ->setParameter('tab2', $etat_production)

                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                        ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                        ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                        ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise
                                ) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
                else{
                    if(count($type_transaction)>1){
                        $liste1 =  $this->createQueryBuilder('d')
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        ->andWhere('d.type_transaction IN(:tab1)')
                        ->setParameter('tab1', $type_transaction)

                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                        ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                        ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                        ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                    else{
                        $liste1 = $this->createQueryBuilder('d')
                        ->getQuery()
                        ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                        
                        ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                        ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                        ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                        ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                    }
                }
            } else if ($t == 2) {
                // on enlève le premier element
                if (in_array("Aucun paiement", $etat_paiement)) {
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                            
                                $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;

                            }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.total_reglement = 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.total_reglement = 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                } else if (in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery() 
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total > d.total_reglement')
                            ->andHaving('d.total_reglement > 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } else if (in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;

                        }
                    }
                }
            } else if ($t == 3) {
                if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement partiel", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 =  $this->createQueryBuilder('d')
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } else if (in_array("Aucun paiement", $etat_paiement) && in_array("Paiement total", $etat_paiement)) {
                    if(count($etat_production)>1){
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                    else{
                        if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                        else{
                            $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                            ->getQuery()
                            ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                        }
                    }
                } else if (in_array("Paiement partiel", $etat_paiement) && in_array("Paiement total", $etat_paiement)) { 
                   if(count($etat_production)>1){
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement <> 0 ')
                                ->getQuery()
                                ->getResult();
                            
                                $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement <> 0 ')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement <> 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.total_reglement <> 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                   else{
                       if(count($type_transaction)>1){
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement <> 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.total_reglement <> 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                       else{
                            $liste1 = $this->createQueryBuilder('d')
                                ->andWhere('d.total_reglement <> 0')
                                ->getQuery()
                                ->getResult();
                            
                            $liste2 = $this->createQueryBuilder('d')
                                ->andWhere('d.total_reglement <> 0')

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeReste != null) {
                                if ($typeReste == "ASC") {
                                    $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                        ->getQuery()
                                        ->getResult();
                                }
                            }
                            if ($typeMontant != null) {
                                if ($typeMontant == "ASC") {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                    ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                    ->getQuery()
                                        ->getResult();
                                }
                            }

                            $Liste = [];
                            foreach ($liste2  as $l2) {
                                $liste_item = [];
                                $ligne_entreprise = [];
                                $son_entreprise = $l2[0]->getEntreprise();
                                foreach ($liste1 as $l1) {
                                    $entreprise_l1 = $l1->getEntreprise();
                                    if ($entreprise_l1 == $son_entreprise) {
                                        array_push($ligne_entreprise, $l1);
                                    }
                                }
                                $liste_item["entreprise"] = $son_entreprise;
                                $liste_item["listes"] = $ligne_entreprise;
                                $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                                $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                                $liste_item["total_reste"] = $l2["total_reste"];

                                array_push($Liste, $liste_item);
                            }
                            return $Liste;
                       }
                   }
                }
            } else if ($t == 4) {
               if(count($etat_production)>1){
                   if(count($type_transaction)>1){
                        $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                        
                        $liste2 =  $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                            ->setParameter('tab2', $etat_production)
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                   }else{
                        $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();

                        $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.etat_production IN(:tab2)')
                            ->setParameter('tab2', $etat_production)
                            ->andWhere('d.montant_total >= 0')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                   }
               }
               else{
                   if(count($type_transaction)>1){
                        $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                        
                            $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.type_transaction IN(:tab1)')
                            ->setParameter('tab1', $type_transaction)
                            ->andWhere('d.montant_total >= 0')

                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
                   }
                   else{
                        $liste1 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total >= 0')
                            ->getQuery()
                            ->getResult();
                        
                        $liste2 = $this->createQueryBuilder('d')
                            ->andWhere('d.montant_total >= 0')
                            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                            ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                            ->groupBy('d.entreprise');
                        if ($typeReglement != null) {
                            if ($typeReglement == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_total_reglement', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeReste != null) {
                            if ($typeReste == "ASC") {
                                $liste2 = $liste2->orderBy('total_reste', 'ASC')
                                    ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('total_reste', 'DESC')
                                    ->getQuery()
                                    ->getResult();
                            }
                        }
                        if ($typeMontant != null) {
                            if ($typeMontant == "ASC") {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'ASC')
                                ->getQuery()
                                    ->getResult();
                            } else {
                                $liste2 = $liste2->orderBy('sous_total_montant_total', 'DESC')
                                ->getQuery()
                                    ->getResult();
                            }
                        }

                        $Liste = [];
                        foreach ($liste2  as $l2) {
                            $liste_item = [];
                            $ligne_entreprise = [];
                            $son_entreprise = $l2[0]->getEntreprise();
                            foreach ($liste1 as $l1) {
                                $entreprise_l1 = $l1->getEntreprise();
                                if ($entreprise_l1 == $son_entreprise) {
                                    array_push($ligne_entreprise, $l1);
                                }
                            }
                            $liste_item["entreprise"] = $son_entreprise;
                            $liste_item["listes"] = $ligne_entreprise;
                            $liste_item["sous_total_montant_total"] = $l2["sous_total_montant_total"];
                            $liste_item["sous_total_total_reglement"] = $l2["sous_total_total_reglement"];
                            $liste_item["total_reste"] = $l2["total_reste"];

                            array_push($Liste, $liste_item);
                        }
                        return $Liste;
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
