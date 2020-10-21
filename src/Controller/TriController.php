<?php

namespace App\Controller;

use App\Services\Services;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TriController extends AbstractController
{
    /**
     * @Route("/profile/tri_heb/{pseudo_hotel}", name="tri_heb")
     */
    public function tri_heb(Request $request, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel,  ClientRepository $repoClient, Services $services)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
            $data_session = $session->get('hotel');
            $data_session['current_page'] = "crj";
            $data_session['pseudo_hotel'] = $pseudo_hotel;
            $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $current_id_hotel = $l_hotel->getId();
            //dd($current_id_hotel);
            $clients = $repoClient->findAll();
            $tab = [];
            foreach ($clients as $item) {
                $son_id_hotel = $item->getHotel()->getId();
                if ($son_id_hotel == $current_id_hotel) {
                    array_push($tab, $item);
                }
            }
            //dd($tab);
            
            $date1_s = $request->get('date1');
            $date2_s = $request->get('date1');

            $date1 = date_create($date1_s);
            $date2 = date_create($date2_s);

            $all_date_asked = $services->all_date_between2_dates($date1, $date2);
            //dd($all_date_asked);
            $tab_aff = [];
           
            foreach ($tab as $client) {
                // on liste tous les jour entre sa dete arrivee et date depart
                $sa_da = $client->getDateArrivee();
                $sa_dd = $client->getDateDepart();
                //dd($sa_dd);
                $his_al_dates = $services->all_date_between2_dates($sa_da, $sa_dd);
               
                //dd($his_al_dates);
                for ($i = 0; $i < count($all_date_asked); $i++) {
                    for ($j = 0; $j < count($his_al_dates); $j++) {
                        if ($all_date_asked[$i] == $his_al_dates[$j]) {
                            if (!in_array($client, $tab_aff)) {
                                array_push($tab_aff, $client);
                            }
                        }
                    }
                }
            }
            //dd($tab_aff);
            $t = [];
            foreach ($tab_aff as $item) { 

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }

           // $data = json_encode($date1->format('d-m-Y')."/". $date2->format('d-m-Y'));
           $data = json_encode($date1_s . "/" . $date2_s);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }

        $data_session = $session->get('hotel');
        $data_session['current_page'] = "crj";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $current_id_hotel = $l_hotel->getId();
        //dd($current_id_hotel);
        $clients = $repoClient->findAll();
        $tab = [];
        foreach($clients as $item){
            $son_id_hotel = $item->getHotel()->getId();
            if($son_id_hotel == $current_id_hotel){
                array_push($tab, $item);
            }
           
        }
        if($request->request->count() > 0){
           // dd($request->request);
            $date1 = $request->request->get('date1');
            $date2 = $request->request->get('date2');

            //dd(gettype($date1)."  ".$date2);

            $date1 = date_create($date1);
            $date2 = date_create($date2);

            
            $all_date_asked = $services->all_date_between2_dates($date1, $date2);
            //dd($all_date_asked);
            $tab_aff = [];
            foreach ($tab as $client) {
                // on liste tous les jour entre sa dete arrivee et date depart
                $sa_da = $client->getDateArrivee();
                $sa_dd = $client->getDateDepart();
                //dd($sa_dd);
                $his_al_dates = $services->all_date_between2_dates($sa_da, $sa_dd);
                //dd($his_al_dates);
                for ($i = 0; $i < count($all_date_asked); $i++) {
                    for ($j = 0; $j < count($his_al_dates); $j++) {
                        if ($all_date_asked[$i] == $his_al_dates[$j]) {
                            if (!in_array($client, $tab_aff)) {
                                array_push($tab_aff, $client);
                            }
                        }
                    }
                }
            }
            //dd($tab_aff);
            $t = [];
            foreach ($tab_aff as $item) {

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }
            //dd($t);
        }
    }

   
}
