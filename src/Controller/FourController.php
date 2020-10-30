<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\Fournisseur;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FournisseurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FourController extends AbstractController
{
    /**
     * @Route("/profile/historique_fournisseur", name="historique_fournisseur")
     */
    public function historique_fournisseur(Services $services, Request $request, HotelRepository $repoHotel, EntityManagerInterface $manager, FournisseurRepository $repoFour)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            if ($date1 != "" && $date2 != "") {
                //$pseudo_hotel = $request->request->get('pseudo_hotel');
                $pseudo_hotel = "royal_beach";
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $current_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);

                $fours = $current_hotel->getFournisseurs();

                $t = [];
                $tab_num_fact = [];
                foreach ($fours as $item) {
                    if (($item->getCreatedAt() >= $date1) && ($item->getCreatedAt() <= $date2)) {
                        if (!in_array($item->getNumeroFacture(), $tab_num_fact)) {
                            array_push($tab_num_fact, $item->getNumeroFacture());
                            array_push($t, ['<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getType() . '</div>', '<div>' . $item->getNomFournisseur() . '</div>', '<div>' . $item->getMontant() . '</div>', '<div>' . $item->getEcheance()->format('d-m-Y') . '</div>', '<div>' . $item->getModePmt() . '</div>', '<div>' . $item->getMontantPaye() . '</div>', '<div>' . $item->getDatePmt()->format('d-m-Y') . '</div>', '<div>' . $item->getRemarque() . '</div>']);
                        }
                    }
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
                
            } else {
                $pseudo_hotel = $request->get('pseudo_hotel');
                $current_id_hotel = $repoHotel->findOneByPseudo($pseudo_hotel)->getId();
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $fours = $current_hotel->getFournisseurs();
                //dd($fours);
                $t = [];
                $tab_num_fact = [];
                foreach ($fours as $item) {
                    if (!in_array($item->getNumeroFacture(), $tab_num_fact)) {
                        array_push($tab_num_fact, $item->getNumeroFacture());
                        array_push($t, ['<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getType() . '</div>', '<div>' . $item->getNomFournisseur() . '</div>', '<div>' . $item->getMontant() . '</div>', '<div>' . $item->getEcheance()->format('d-m-Y') . '</div>', '<div>' . $item->getModePmt() . '</div>', '<div>' . $item->getMontantPaye() . '</div>', '<div>' . $item->getDatePmt()->format('d-m-Y') . '</div>', '<div>' . $item->getRemarque() . '</div>']);
                    }
                }
                //dd($t);
                                
                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
        else{
           
            $date1 = "21-05-2020";
            $date2 = "27-05-2020";
           if ($date1 != "" && $date2 != "") {
                //$pseudo_hotel = $request->request->get('pseudo_hotel');
                $pseudo_hotel = "royal_beach";
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $current_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);

                $fours = $current_hotel->getFournisseurs();

                $t = [];
                $tab_num_fact = [];
                foreach ($fours as $item) {
                    if(($item->getCreatedAt() >= $date1) && ($item->getCreatedAt() <= $date2)){
                        if (!in_array($item->getNumeroFacture(), $tab_num_fact)) {
                            array_push($tab_num_fact, $item->getNumeroFacture());
                            array_push($t, ['<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getType() . '</div>', '<div>' . $item->getNomFournisseur() . '</div>', '<div>' . $item->getMontant() . '</div>', '<div>' . $item->getEcheance()->format('d-m-Y') . '</div>', '<div>' . $item->getModePmt() . '</div>', '<div>' . $item->getMontantPaye() . '</div>', '<div>' . $item->getDatePmt()->format('d-m-Y') . '</div>', '<div>' . $item->getRemarque() . '</div>']);
                        }
                    }
                   
                }
                
              
             dd($t);
           }              
        }
    }
}
