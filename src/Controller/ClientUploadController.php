<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\Fournisseur;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FournisseurRepository;
use App\Repository\ClientUploadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientUploadController extends AbstractController
{
    /**
     * @Route("/profile/historique_client_upload", name="historique_client_upload")
     */
    public function historique_client_upload(Services $services, Request $request, HotelRepository $repoHotel, EntityManagerInterface $manager, ClientUploadRepository $repoCup)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            if ($date1 != "" && $date2 != "") {
                $pseudo_hotel = $request->request->get('pseudo_hotel');
                //$pseudo_hotel = "royal_beach";
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $current_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);

                $cups = $current_hotel->getClientUploads();

                $t = [];
                $tab_num_fact = [];
                foreach ($cups as $item) {
                    if (($item->getDate() >= $date1) && ($item->getDate() <= $date2)) {
                        if (!in_array($item->getNumeroFacture(), $tab_num_fact)) {
                            $date = $item->getDate();
                            if ($date != "") {
                                $date = $date->format("d-m-Y");
                            }
                            $date_pmt = $item->getDatePmt();
                            if ($date_pmt != "") {
                                $date_pmt = $date_pmt->format("d-m-Y");
                            }
                            array_push($tab_num_fact, $item->getNumeroFacture());
                            array_push($t, ['<div>' . $item->getAnnee() . '</div>', '<div>' . $item->getTypeClient() . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getNom() . '</div>', '<div>' . $item->getPersonneHebergee() . '</div>', '<div>' . $services->to_money($item->getMontant()) . '</div>', '<div>' . $date . '</div>', '<div>' . $services->to_money($item->getMontantPayer()) . '</div>', '<div>' . $date_pmt . '</div>', '<div>' . $item->getModePmt() . '</div>']);
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
                $cups = $current_hotel->getClientUploads();
                //dd($fours);
                $t = [];
                $tab_num_fact = [];
                foreach ($cups as $item) {
                    if (!in_array($item->getNumeroFacture(), $tab_num_fact)) {
                        $date = $item->getDate();
                        if($date != ""){
                            $date = $date->format("d-m-Y");
                        }
                        $date_pmt = $item->getDatePmt();
                        if($date_pmt != ""){
                            $date_pmt = $date_pmt->format("d-m-Y");
                        }
                        array_push($tab_num_fact, $item->getNumeroFacture());
                        array_push($t, ['<div>' . $item->getAnnee() . '</div>', '<div>' . $item->getTypeClient() . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getNom() . '</div>', '<div>' . $item->getPersonneHebergee() . '</div>', '<div>' . $services->to_money($item->getMontant()) . '</div>', '<div>' . $date . '</div>', '<div>' . $services->to_money($item->getMontantPayer()) . '</div>', '<div>' . $date_pmt . '</div>', '<div>' . $item->getModePmt() . '</div>']);
                    }
                }
                //dd($t);

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        } 
       
    }
}
