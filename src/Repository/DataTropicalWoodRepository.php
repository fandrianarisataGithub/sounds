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
    public function searchEntrepriseContact(array $liste, $tri_reglement, $tri_reste, $tri_montant)
    {   $Liste = [];
        // on tri les $liste selon les critère d'abord

        $liste1 = $this->createQueryBuilder('d');
        for ($i = 1; $i < count($liste); $i++) {
            $liste1 = $liste1->orWhere('d.entreprise =:val'.$i)
                            ->setParameter('val'.$i, $liste[$i]);
        }

           
            $liste1 = $liste1->getQuery()
                    ->getResult();

        $liste2 = $this->createQueryBuilder('d');
        for ($i = 1; $i < count($liste); $i++) {
            $liste2 = $liste2->orWhere('d.entreprise =:val' . $i)
                ->setParameter('val' . $i, $liste[$i]);
        }


        $liste2 = $liste2
            ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
            ->addSelect('SUM(d.montant_total) as sous_total_montant_total')
            ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste');
        
            if($tri_reglement){
                $liste2 = $liste2->orderBy('sous_total_total_reglement', $tri_reglement);
            }
             if($tri_reste){
                $liste2 = $liste2->orderBy('total_reste', $tri_reste);
            }
             if($tri_montant){
                $liste2 = $liste2->orderBy('sous_total_montant_total', $tri_montant);
            }
            
            $liste2 = $liste2->groupBy('d.entreprise')
            ->getQuery()
            ->getResult();
        
        //dd($liste2);
        
        for($i = 0 ; $i< count($liste2); $i++){
            $liste_item = [];
            $tab_temp = [];
            $entreprise = $liste2[$i][0]->getEntreprise();   
            $liste_item["entreprise"] = $entreprise;
            foreach($liste1 as $item){
                $en = $item->getEntreprise();
                if($en == $entreprise){
                    array_push($tab_temp, $item);
                }
            }
            
            $liste_item["listes"] = $tab_temp;
            $liste_item["sous_total_montant_total"] = $liste2[$i]["sous_total_montant_total"];
            $liste_item["sous_total_total_reglement"] = $liste2[$i]["sous_total_total_reglement"];
            $liste_item["total_reste"] = $liste2[$i]["total_reste"];
            
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
            $date3,
            $date4,
            array $type_transaction, 
            array $etat_production, 
            array $etat_paiement,
            $typeReglement,
            $typeReste,
            $typeMontant
        )
    {   
       
        if($date1 != "" && $date2 != ""){
            // si il n'y avait pas de date de fact
            if($date3 == "" && $date4 == ""){
                $t = count($etat_paiement);
                if ($t == 1) {
                    // tsisy zany
                    if (count($type_transaction) > 1) {
                        if (count($etat_production) > 1) {
                            $liste1 =  $this->createQueryBuilder('d')
                                ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
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
                        } else {
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
                    } else {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
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
                    } else if (in_array("Paiement partiel", $etat_paiement)) {
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                        } else {
                            if (count($type_transaction) > 1) {
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
                            } else {
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
                    if (count($etat_production) > 1) {
                        if (count($type_transaction) > 1) {
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
                        } else {
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
                    } else {
                        if (count($type_transaction) > 1) {
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
                        } else {
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
            // s'il y a de date de fact
            else if($date3 != "" && $date4 != "") {
                    $t = count($etat_paiement);
                    if ($t == 1) {
                        // tsisy zany
                        if (count($type_transaction) > 1) {
                            if (count($etat_production) > 1) {
                                $liste1 =  $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->getQuery()
                                    ->getResult();

                                $liste2 =  $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                        } else {
                            if (count($etat_production) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                        ->setParameter('date3', $date3)
                                        ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.type_transaction IN(:tab1)')
                                        ->setParameter('tab1', $type_transaction)
                                        ->andWhere('d.total_reglement = 0')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.total_reglement = 0')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else if (in_array("Paiement partiel", $etat_paiement)) {
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.montant_total > d.total_reglement')
                                        ->andHaving('d.total_reglement > 0')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.etat_production IN(:tab2)')
                                        ->setParameter('tab2', $etat_production)
                                        ->andWhere('d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.type_transaction IN(:tab1)')
                                        ->setParameter('tab1', $type_transaction)
                                        ->andWhere('d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.etat_production IN(:tab2)')
                                        ->setParameter('tab2', $etat_production)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.type_transaction IN(:tab1)')
                                        ->setParameter('tab1', $type_transaction)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 =  $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.etat_production IN(:tab2)')
                                        ->setParameter('tab2', $etat_production)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.type_transaction IN(:tab1)')
                                        ->setParameter('tab1', $type_transaction)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            if (count($etat_production) > 1) {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.etat_production IN(:tab2)')
                                        ->setParameter('tab2', $etat_production)
                                        ->andWhere('d.montant_total >= d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                if (count($type_transaction) > 1) {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.type_transaction IN(:tab1)')
                                        ->setParameter('tab1', $type_transaction)
                                        ->andWhere('d.montant_total >= d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                } else {
                                    $liste1 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                        ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                        ->setParameter('date1', $date1)
                                        ->setParameter('date2', $date2)
                                        ->andWhere('d.montant_total >= d.total_reglement')
                                        ->getQuery()
                                        ->getResult();

                                    $liste2 = $this->createQueryBuilder('d')
                                        ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.montant_total >= 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total >= 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                    ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->setParameter('date1', $date1)
                                    ->setParameter('date2', $date2)
                                    ->andWhere('d.montant_total >= 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_confirmation BETWEEN :date1 AND :date2')
                                ->andWhere('d.date_facture BETWEEN :date3 AND :date4')
                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
        }
        // fin si date1 et date2 exist 

        // debut si date3 et date4 exist 

        if ($date3 != "" && $date4 != "") {
            if($date1 == "" && $date2 == ""){
                $t = count($etat_paiement);
                if ($t == 1) {
                    // tsisy zany
                    if (count($type_transaction) > 1) {
                        if (count($etat_production) > 1) {
                            $liste1 =  $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->getQuery()
                                ->getResult();

                            $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if ($typeReglement != null) {
                                if ($typeReglement == "ASC") {
                                    $liste2 = $liste2->orderBy(
                                        'sous_total_total_reglement',
                                        'ASC'
                                    )
                                        ->getQuery()
                                        ->getResult();
                                } else {
                                    $liste2 = $liste2->orderBy(
                                        'sous_total_total_reglement',
                                        'DESC'
                                    )
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
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)

                                ->addSelect('SUM(d.total_reglement) as sous_total_total_reglement')
                                ->addSelect('SUM(d.montant_total)as sous_total_montant_total')
                                ->addSelect('SUM(d.montant_total - d.total_reglement) as total_reste')
                                ->groupBy('d.entreprise');
                            if (
                                $typeReglement != null
                            ) {
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                    } else {
                        if (count($etat_production) > 1) {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)

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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                   
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab1)')
                                    ->setParameter('tab1', $etat_production)
                                    ->andWhere('d.total_reglement = 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                    
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.total_reglement = 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total > d.total_reglement')
                                    ->andHaving('d.total_reglement > 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.montant_total > d.total_reglement')
                                    ->andHaving('d.total_reglement > 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total > d.total_reglement')
                                    ->andHaving('d.total_reglement > 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.montant_total > d.total_reglement')
                                    ->andHaving('d.total_reglement > 0')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                    if (
                        in_array("Aucun paiement", $etat_paiement) && in_array("Paiement partiel", $etat_paiement)
                    ) {
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total > d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.total_reglement = 0 OR d.montant_total = d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                    ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                    } else if (
                        in_array("Paiement partiel", $etat_paiement) && in_array("Paiement total", $etat_paiement)
                    ) {
                        if (count($etat_production) > 1) {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                    ->setParameter('tab2', $etat_production)
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total >= d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 =  $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.etat_production IN(:tab2)')
                                    ->setParameter('tab2', $etat_production)
                                    ->andWhere('d.montant_total >= d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                        } else {
                            if (count($type_transaction) > 1) {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.type_transaction IN(:tab1)')
                                    ->setParameter('tab1', $type_transaction)
                                    ->andWhere('d.montant_total >= d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                            } else {
                                $liste1 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
                                    ->andWhere('d.montant_total >= d.total_reglement')
                                    ->getQuery()
                                    ->getResult();

                                $liste2 = $this->createQueryBuilder('d')
                                     ->Where('d.date_facture BETWEEN :date3 AND :date4')
                                    
                                    ->setParameter('date3', $date3)
                                    ->setParameter('date4', $date4)
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
                } else if (
                    $t == 4
                ) {
                    if (count($etat_production) > 1) {
                        if (count($type_transaction) > 1) {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.etat_production IN(:tab2) AND d.type_transaction IN(:tab1)')
                                ->setParameter('tab2', $etat_production)
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total >= 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.etat_production IN(:tab2)')
                                ->setParameter('tab2', $etat_production)
                                ->andWhere('d.montant_total >= 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                    } else {
                        if (count($type_transaction) > 1) {
                            $liste1 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.type_transaction IN(:tab1)')
                                ->setParameter('tab1', $type_transaction)
                                ->andWhere('d.montant_total >= 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
                                ->andWhere('d.montant_total >= 0')
                                ->getQuery()
                                ->getResult();

                            $liste2 = $this->createQueryBuilder('d')
                                ->Where('d.date_facture BETWEEN :date3 AND :date4')

                                ->setParameter('date3', $date3)
                                ->setParameter('date4', $date4)
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
                            if (
                                $typeReste != null
                            ) {
                                if (
                                    $typeReste == "ASC"
                                ) {
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
                                if (
                                    $typeMontant == "ASC"
                                ) {
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

        // fin s date3 et date4 exist
        else if($date1 == "" && $date2 == "" && $date3 == "" && $date4 == ""){
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



    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function findLastinsert()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function listeAllResultCheckup()
    {
        return $this->createQueryBuilder('d')
            ->addSelect("d.idPro, d.entreprise")
            ->where("d.etat_production ='facturé'")
            ->andWhere("d.reste > 0")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return DataTropicalWood[] 
     */
   /* public function findById_pro($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.idPro = :val')
            ->setParameter('val', $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }*/
    
    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
    */
    public function listeResultOfCheckupByEntreprise()
    {
        $result_gb = $this->createQueryBuilder('d')
            ->addSelect("d.entreprise, d.idPro, SUM(d.reste) AS total_reste, SUM(d.montant_total) AS total_montant_total, SUM(d.total_reglement) AS total_total_reglement")
            ->where("d.reste > 0")
            ->andWhere("d.etat_production = 'facturé'")
            ->groupBy("d.entreprise")
            ->orderBy("total_reste", "DESC")
            ->getQuery()
            ->getResult()
        ;
        //dd($result_gb);
        $liste_by_id_pro = $this->listeAllResultCheckup();
        $resultat = [];
        $i = 0;
        foreach($result_gb as $rgb){
            // on cherche entreprise_id
            $conn = $this->getEntityManager()->getConnection();
            $sql = '
                SELECT id FROM entreprise_tw WHERE nom = :val
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['val' => $rgb["entreprise"]]);

            $entreprise_id = $stmt->fetch();

            $tab = [
                "index"                     => $i++,
                "entreprise"                => $rgb["entreprise"],
                "liste_trans_ter"           => $this->findAllListeTransTer($rgb["entreprise"]),
                "liste_trans_enc"           => $this->findAllListeTransEnc($rgb["entreprise"]),
                "total_reste"               => $rgb["total_reste"],
                "total_montant_total"       => $rgb["total_montant_total"],
                "total_total_reglement"     => $rgb["total_total_reglement"],
                "liste_client_en_attente"   => [],
                "liste_contact"             => $this->find_all_contact($entreprise_id),
                "liste_remarque_facture"    => $this->find_all_remarque_facture($entreprise_id),
                "liste_remarque_enc"        => $this->find_all_remarque_enc($entreprise_id),
                "liste_remarque_ter"        => $this->find_all_remarque_ter($entreprise_id),
            ];
            foreach($liste_by_id_pro as $residp){
                
                if($rgb["entreprise"] == $residp["entreprise"]){
                    // le idPro du courant $reidp
                    $id_pro =  $residp["idPro"];
                    $client = $this->findOneByIdPro($id_pro);
                    array_push($tab["liste_client_en_attente"], $client);
                }
            }
            array_push($resultat, $tab);
        }
        return $resultat;
    }

    public function findAllListeTransTer($nom_client){
        return $this->createQueryBuilder('d')
            ->andWhere('d.entreprise = :val')
            ->andWhere('d.reste = :val2')
            ->setParameter('val', $nom_client)
            ->setParameter('val2', '0.0')
            ->getQuery()
            ->getResult();
    }

    public function findAllListeTransEnc($nom_client){
        return $this->createQueryBuilder('d')
            ->andWhere('d.entreprise = :val')
            ->andWhere('d.reste > 0')
            ->andWhere("d.etat_production <> 'facturé'")
            ->setParameter('val', $nom_client)
            ->getQuery()
            ->getResult();
    }

    public function find_all_contact($entreprise_id){
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM contact_entreprise_tw WHERE entreprise_id = :val
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['val' => $entreprise_id['id']]);
        return $stmt->fetchAll();
        
    }

    public function find_all_remarque_enc($entreprise_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM `remarque_entreprise_tw` WHERE entreprise_id = :val AND etat_resultat = :etat
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
                        'val'   => $entreprise_id['id'],
                        'etat'  => 'en cours'
                        ]);
        return $stmt->fetchAll();
    }

    public function find_all_remarque_facture($entreprise_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM `remarque_entreprise_tw` WHERE entreprise_id = :val AND etat_resultat = :etat
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
                        'val'   => $entreprise_id['id'],
                        'etat'  => 'facturé'
                        ]);
        return $stmt->fetchAll();
    }

    public function find_all_remarque_ter($entreprise_id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT * FROM `remarque_entreprise_tw` WHERE entreprise_id = :val AND etat_resultat = :etat
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
                        'val'   => $entreprise_id['id'],
                        'etat'  => '1'
                        ]);
        return $stmt->fetchAll();
    }

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
