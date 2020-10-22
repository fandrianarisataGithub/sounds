<?php

namespace App\Controller;

use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    /**
     * @Route("/profile/select_client/today", name="listing_client")
     */
    public function listing_client(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $pseudo_hotel = $request->get('pseudo_hotel');
            // $pseudo_hotel = "royal_beach";
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $id_hotel = $hotel->getId();
            $clients = $repoClient->findAll();
            $clients_today = [];
            $today = new \DateTime();
            $today_s = $today->format('d-m-Y');
            $t = [];
            foreach ($clients as $c) {
                $son_hotel = $c->getHotel();
                $son_id_hotel = $son_hotel->getId();
                $son_createdAt = $c->getCreatedAt();
                $son_createdAt_s = $son_createdAt->format('d-m-Y');
                if ($son_createdAt_s == $today_s && $son_id_hotel == $id_hotel) {
                    array_push($clients_today, $c);
                }
                // $clients_today est encore un tab d'objet
                //dd($clients_today);
                //dd($t);
            }
            foreach ($clients_today as $item) {

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }
            $data = json_encode($t);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
           
        }
        //$pseudo_hotel = $request->request->get('pseudo_hotel');
        $pseudo_hotel = "royal_beach";
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $id_hotel = $hotel->getId();
        $clients = $repoClient->findAll();
        $clients_today = [];
        $today = new \DateTime();
        $today_s = $today->format('d-m-Y');
        $t = [];
        foreach ($clients as $c) {
            $son_hotel = $c->getHotel();
            $son_id_hotel = $son_hotel->getId();
            $son_createdAt = $c->getCreatedAt();
            $son_createdAt_s = $son_createdAt->format('d-m-Y');
            if ($son_createdAt_s == $today_s && $son_id_hotel == $id_hotel) {
                array_push($clients_today, $c);
            }
            // $clients_today est encore un tab d'objet
            //dd($clients_today);
            foreach ($clients_today as $item) {

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }
            //dd($t);
        }
    }

    /**
     * @Route("/profile/select_current_client", name="listing_current_client")
     */
    public function listing_current_client(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {
            $pseudo_hotel = $request->get('pseudo_hotel');
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $clients = $repoClient->findAll();
            $clients_current = [];
            $today = new \DateTime();

            $today_s = $today->format('d-m-Y');
            $today = date_create($today_s);
           
            $t = [];
            $x = 0;
            foreach ($clients as $c) {
                $son_hotel = $c->getHotel();
                $son_pseudo_hotel = $son_hotel->getPseudo();
                if ($son_pseudo_hotel == $pseudo_hotel) {
                    $sa_date_arrivee = $c->getDateArrivee();
                    $sa_date_arrivee = $sa_date_arrivee->format("d-m-Y");
                    $sa_date_arrivee = date_create($sa_date_arrivee);
                    $sa_date_depart = $c->getDateDepart();
                    $sa_date_depart = $sa_date_depart->format("d-m-Y");
                    $sa_date_depart = date_create($sa_date_depart);
                    if (($sa_date_arrivee <= $today) && ($today <= $sa_date_depart)) {
                        array_push($clients_current, $c);
                    } else {
                        $x = $x;
                    }
                }
            }
            foreach ($clients_current as $item) {

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }
            $data = json_encode($t);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
        //$pseudo_hotel = $request->request->get('pseudo_hotel');
        $pseudo_hotel = "royal_beach";
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $id_hotel = $hotel->getId();
        $clients = $repoClient->findAll();
        $clients_today = [];
        $today = new \DateTime();
        
        $today_s = $today->format('d-m-Y');
        $today = date_create($today_s);
        //dd($today);
        $t = [];
        $x = 0;
        foreach ($clients as $c) {
            $son_hotel = $c->getHotel();
            $son_pseudo_hotel = $son_hotel->getPseudo();
            if($son_pseudo_hotel == $pseudo_hotel){
                $sa_date_arrivee = $c->getDateArrivee();
                $sa_date_arrivee = $sa_date_arrivee->format("d-m-Y");
                $sa_date_arrivee = date_create($sa_date_arrivee);
                $sa_date_depart = $c->getDateDepart();
                $sa_date_depart = $sa_date_depart->format("d-m-Y");
                $sa_date_depart = date_create($sa_date_depart);
                if (($sa_date_arrivee <= $today) && ($today <= $sa_date_depart)) {
                    array_push($clients_today, $c);
                } else {
                    $x = $x;
                }
            }
            
        }
        foreach ($clients_today as $item) {

            array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div>' . $item->getCreatedAt()->format("d-m-Y") . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getDureeSejour(), '<div class="text-start"><a href="#" data-id = "' . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-id = "' . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
        }
        dd($clients_today);
    }

    /**
     * @Route("/admin/suprimer_client", name = "delete_client")
     */
    public function delete_client(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $client_id = $request->get('client_id');

            $client = $repoClient->find($client_id);
            $manager->remove($client);
            $manager->flush();

            $data = json_encode("deleted");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/admin/check_client", name = "check_client")
     */
    public function check_client(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $client_id = $request->get('client_id');
            $client = $repoClient->find($client_id);
            $html = '';
            $html.= '<form action="/admin/modifier_client_tri" method = "POST">
                        <input type="hidden" id = "hidden_date1" name = "date1">
                        <input type="hidden" id = "hidden_date2" name = "date2">
                        <input type="hidden" id = "hidden_id" value = "'. $client->getId() .'" name = "client_id">
                        <div class="form-group">
                            <label for="n_date_depart">Nom du client :
                            </label>
                            <input type="text" id="modif_nom_client" class="form-control" value = "' . $client->getNom() . '" name = "nom">
                        </div>
                            <div class="form-group">
                            <label for="n_date_depart">Prénom du client :
                            </label>
                            <input type="text" id="modif_prenom_client" class="form-control" value = "' . $client->getPrenom() . '" name = "prenom">
                        </div>
                        <div class="form-group">
                            <label for="n_date_arrivee">Date d \'arrivée :
                            </label>
                            <input type="date" id="modif_date_arrivee" class="form-control" value = "' . $client->getDateArrivee()->format("Y-m-d") . '" name = "date_arrivee">
                        </div>
                        <div class="form-group">
                            <label for="n_date_depart">Date du départ :
                            </label>
                            <input type="date" id="modif_date_depart" class="form-control" value = "' . $client->getDateDepart()->format("Y-m-d") . '" name = "date_depart">
                        </div>
                        <div class="form-group">
                           <button type = "submit" class="form-control btn btn-warning"><span>Enregistrer</span></button>
                        </div>
                    </form>';

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
    /**
     * @Route("/admin/edit_client", name = "edit_client")
     */
    public function edit_client(Request $request, EntityManagerInterface $manager, ClientRepository $repoClient)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $id = $request->get('id');
            $nom = $request->get('nom');
            $prenom = $request->get('prenom');
            $date_arrivee = $request->get('date_arrivee');
            $date_depart = $request->get('date_depart');
            $date_arrivee = date_create($date_arrivee);
            $date_depart = date_create($date_depart);
            $diff = $date_arrivee->diff($date_depart);
            $days = $diff->d;
            // on pick le client

            $client = $repoClient->find($id);
            $client->setNom($nom);
            $client->setPrenom($prenom);
            $client->setDateArrivee($date_arrivee);
            $client->setDateDepart($date_depart);
            $client->setDureeSejour($days);
            $manager->persist($client);
            $manager->flush();

            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    
}
