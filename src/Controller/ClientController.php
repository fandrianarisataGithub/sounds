<?php

namespace App\Controller;

use App\Entity\Visit;
use App\Entity\Client;
use App\Entity\Customer;
use App\Services\Services;
use App\Entity\Fidelisation;
use App\Repository\HotelRepository;
use App\Repository\VisitRepository;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FidelisationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    private $repoClient;
    private $repoFid;
    private $repoCust;
    private $repoVisit;
    private $manager;
    private $startFidelisation; // date de debut de la catégorisation
    private $services;

    public function __construct(VisitRepository $repoVisit, CustomerRepository $repoCust, ClientRepository $repoClient, FidelisationRepository $repoFid, EntityManagerInterface $manager, Services $services)
    {
        $this->startFidelisation = date_create("2021-01-01"); 
        $this->repoClient = $repoClient;
        $this->manager = $manager;
        $this->repoFid = $repoFid;
        $this->services = $services;
        $this->repoCust = $repoCust;
        $this->repoVisit = $repoVisit;
    }

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
            $response->headers->set('Access-Control-Allow-Origin', '*');
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
    public function listing_current_client(Services $services, Request $request, VisitRepository $repoVisit, HotelRepository $repoHotel, CustomerRepository $repoCust)
    {
        $response = new Response();
        
        if ($request->isXmlHttpRequest()) {
            $pseudo_hotel = $request->get('pseudo_hotel');
            $hotel = $repoHotel->findOneByPseudo(['pseudo' => $pseudo_hotel]);
            $date = $request->get('date');
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $today = date_create($date);
            $tab = [];
            $tab_aff = [];
            if($date1 == "" && $date2 == ""){
                $tab = $repoVisit->findCustomersByVisit($hotel);
                foreach ($tab as $client) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    
                    $sa_da = $client[0]->getCheckin();
                    $sa_dd = $client[0]->getCheckout();
                    if ($today <= $sa_dd && $today >= $sa_da
                    ) {
                        //dd('tato le');
                        array_push($tab_aff, $client);
                    }
                }
            
               
            }
            else if($date1 != "" && $date2 != ""){
                //$tab = $repoClient->findClientBetweenTwoDates();

                $date1 = date_create($date1);
                $date2 = date_create($date2);

                $all_date_asked = $services->all_date_between2_dates($date1, $date2);
                //dd($all_date_asked);
                if($date1 == $date2){
                    $all_date_asked = [0 => $date1->format('Y-m-d')];
                }
                
                $tab = $repoVisit->findCustomersByVisit($hotel);
               
                foreach ($tab as $client) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $sa_da = $client[0]->getCheckin();
                    $sa_dd = $client[0]->getCheckout();
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
            }
            
            $t = [];
            //dd($tab_aff);
            foreach ($tab_aff as $item) {
                $contact = ($item['email'] ? $item['email'] : $item['telephone']);
                array_push($t, ['<div>' . $item['name'] . '</div><div>' . $item['lastName'] . '</div><div><span>Contact : </span>' 
                . $contact . '</div>', $item[0]->getCheckin()->format('d-m-Y'), $item[0]->getCheckout()->format('d-m-Y'), $item[0]->getProvenance(), $item[0]->getSource(), $item[0]->getNbrNuitee(), $item[0]->getNbrChambre(), '<span class="montant">' 
                .$item[0]->getMontant().'</span>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' 
                . $item[0]->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' 
                . $item[0]->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
            }

            $data = json_encode($t);
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);
            return $response;
        }
        
    }

    /**
     * @Route("/profile/storeclient/{pseudo_hotel}", name="store_client")
    */
    public function storeClient(
        HotelRepository $repoHotel, 
        ClientRepository $repoClient,
        CustomerRepository $repoCust,
        VisitRepository $repoVisit,
        Request $request,
        $pseudo_hotel)
    {
        $response = new Response();
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $today = new \DateTime();
        if ($request->isXmlHttpRequest()) {

            $nom_client = $request->get('nom_client');
            $tarif_client = $request->get('tarif_client');
            $prenom_client = (empty($request->get('prenom_client'))) ? "" : $request->get('prenom_client');
            $date_arrivee = $request->get('date_arrivee');
            $date_depart = $request->get('date_depart');
            $nbr_chambre = $request->get('nbr_chambre');
            $provenance = $request->get('provenance');
            $email = $request->get('email');
            $telephone = $request->get('telephone');
            $tab_identifiant = ["mail" => $email, "telephone" => $telephone];
            $source = $request->get('source');
            $prix_total = $request->get('prix_total');
            //dd($prix_total);
            $createdAt = date_create($request->get('createdAt'));
            $diff = "";
            $days = "";
            
            $t = ['0','1','2', '3', '4', '5', '6', '7', '8', '9'];
            // start client insertiion
            if(!empty($date_depart) && !empty($date_arrivee)){
                $date_arrivee = date_create($date_arrivee);
                $date_depart = date_create($date_depart);
                if($date_arrivee > $date_depart){
                    $data = json_encode("Il faut que la date de checkin soit une date inférieur à celle de checkout"); 
                    $response->headers->set('Content-Type', 'application/json');
                    $response->headers->set('Access-Control-Allow-Origin', '*');
                    $response->setContent($data);
        
                    // end client insertion in fidelisation
        
                    return $response;
                }
                $diff = $date_arrivee->diff($date_depart);
                $days = $diff->days;
                //dd($days);
            }
            
            if(!empty($email) && $email != "0"){
                //dd("juju");
                if(filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email[0], $t)){
                    
                    if(!empty($nom_client) 
                            && !empty($date_depart) 
                            && !empty($date_arrivee)
                            && !empty($prix_total) 
                            && !empty($nbr_chambre)
                            && !empty($source)
                    ){  
                        
                        // on cherche si le client est déjà dans la base 
                        $custumer = $repoCust->findCustByData([
                            "name" => $nom_client,
                            "lastName" => $prenom_client,
                            "email" => $email,
                            "telephone"=> $telephone
                        ]);
                        
                        // si ses tests sont tous null on crée un nouveau customer and visit after

                        if(!$custumer){
                            $client = new Customer();
                            $client->setName($nom_client);
                            $client->setLastName($prenom_client);
                            $client->setEmail($email);                           
                            $client->setTelephone($telephone);
                            $client->setCreatedAt($createdAt);
                            $this->manager->persist($client);
                            // on crée la data visit
                            $visit = new Visit();
                            $visit->setCustomer($client);
                            $visit->setHotel($hotel);
                            $visit->setCheckin($date_arrivee);
                            $visit->setCheckout($date_depart);
                            $visit->setNbrNuitee($days);
                            $visit->setSource($source);   
                            $visit->setProvenance($provenance);
                            $visit->setNbrChambre($nbr_chambre);
                            $visit->setMontant(str_replace(" ", "", $prix_total));
                            $visit->setCreatedAt(date_create($today->format('Y-m-d')));
                            $this->manager->persist($visit);
                            $this->manager->flush();
                            
                            $this->categorisation($client);
                            $data = json_encode("ok"); 
                        }
                        else if($custumer){
                            $visit = new Visit();
                            $visit->setCustomer($custumer);
                            $visit->setHotel($hotel);
                            $visit->setCheckin($date_arrivee);
                            $visit->setCheckout($date_depart);
                            $visit->setNbrNuitee($days);
                            $visit->setSource($source);   
                            $visit->setProvenance($provenance);
                            $visit->setNbrChambre($nbr_chambre);
                            $visit->setMontant(str_replace(" ", "", $prix_total));
                            $visit->setCreatedAt(date_create($today->format('Y-m-d')));
                            $this->manager->persist($visit);
                            $this->manager->flush();
                            
                            $this->categorisation($custumer);
                            $data = json_encode("ok");
                        }

                    }
                    else{
                        $data = json_encode("Veuiller renseigner au moins les champs Nom, Check in, check out, Source, Nbr chambres, Prix total"); 
                    }
                }else{
                    $data = json_encode("Veuiller entrer un adresse email valide"); 
                }
            }else if(empty($email) || $email == "0"){
                //dd("ato e");
                if(!empty($nom_client) 
                        && !empty($date_depart) 
                        && !empty($date_arrivee)
                        && !empty($prix_total) 
                        && !empty($nbr_chambre)
                        && !empty($telephone)
                        && !empty($source)
                ){
                    
                   // on cherche si le client est déjà dans la base 
                    $custumer = $repoCust->findCustByData([
                        "name" => $nom_client,
                        "lastName" => $prenom_client,
                        "email" => $email,
                        "telephone"=> $telephone
                    ]);

                    //dd($custumer);
                // si ses tests sont tous null on crée un nouveau customer and visit after

                if(!$custumer){
                    $client = new Customer();
                    $client->setName($nom_client);
                    $client->setLastName($prenom_client);
                                              
                    $client->setTelephone($telephone);
                    $client->setCreatedAt($createdAt);
                    $this->manager->persist($client);
                    // on crée la data visit
                    $visit = new Visit();
                    $visit->setCustomer($client);
                    $visit->setHotel($hotel);
                    $visit->setCheckin($date_arrivee);
                    $visit->setCheckout($date_depart);
                    $visit->setNbrNuitee($days);
                    $visit->setSource($source);   
                    $visit->setProvenance($provenance);
                    $visit->setNbrChambre($nbr_chambre);
                    $visit->setMontant(str_replace(" ", "", $prix_total));
                    $visit->setCreatedAt(date_create($today->format('Y-m-d')));
                    $this->manager->persist($visit);
                    $this->manager->flush();
                    
                    $this->categorisation($client);
                    $data = json_encode("ok"); 
                }
                else if($custumer){
                    $visit = new Visit();
                    $visit->setCustomer($custumer);
                    $visit->setHotel($hotel);
                    $visit->setCheckin($date_arrivee);
                    $visit->setCheckout($date_depart);
                    $visit->setNbrNuitee($days);
                    $visit->setSource($source);   
                    $visit->setProvenance($provenance);
                    $visit->setNbrChambre($nbr_chambre);
                    $visit->setMontant(str_replace(" ", "", $prix_total));
                    $visit->setCreatedAt(date_create($today->format('Y-m-d')));
                    $this->manager->persist($visit);
                    $this->manager->flush();
                    
                    $this->categorisation($custumer);
                    $data = json_encode("ok");
                }
                }
                else{
                    $data = json_encode("Veuiller renseigner au moins les champs Nom, Check in, check out, Nbr chambres, Source, Prix total et un contact");  
                }
                
            }
           
            // condition 

            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);

            // end client insertion in fidelisation

            return $response;
        }
    }

    public function setCategoriesForClient($client, $nameOfFid){
        $fidelisation = $this->repoFid->findOneBy(['nom'=>$nameOfFid]);
            $client->setFidelisation($fidelisation);
        
        $this->manager->flush();
    }

    /**
     * 
     * @Route("/profile/liste_visit", name="list_visit")
    */
    public function categorisation(?Customer $customer) :void
    {   
       
        if($customer){
            // on cherche la date de la dernière visite
            $last_visit = $this->repoVisit->findLastDateVisit($customer);
            //dd($last_visit);
            $date_last_visit = $last_visit[0]->getCreatedAt();
            //dd($date_last_visit);
            $diff_year = date_diff($date_last_visit, new \DateTime())->y;
            // déjà si diff_year >= 1 ... on remet le client en btn_cardex
            if($diff_year >= 1){
                $this->setCategoriesForClient($customer, "cardex");
            }
            else if($diff_year < 1){
                // on compte les montant
                $somme_nuitee = 0;
                $visits = $customer->getVisits();
                foreach($visits as $visit){
                    $somme_nuitee = $somme_nuitee + intval($visit->getNbrNuitee());
                }
                // les fids
                $fids = $this->repoFid->findAll();
                //catégorisation
                if( $somme_nuitee >= 0 &&  $somme_nuitee <= $fids[0]->getLimiteNuite()){
                    $this->setCategoriesForClient($customer, "cardex");
                }
                if( $somme_nuitee >= (intval($fids[0]->getLimiteNuite()) + 1) &&  $somme_nuitee <= $fids[1]->getLimiteNuite()){
                    $this->setCategoriesForClient($customer, "preferentiel");
                }
                if( $somme_nuitee >= (intval($fids[1]->getLimiteNuite()) + 1) &&  $somme_nuitee <= $fids[2]->getLimiteNuite()){
                    $this->setCategoriesForClient($customer, "privilege");
                }
                if( $somme_nuitee >= (intval($fids[2]->getLimiteNuite()) + 1)){ 
                    $this->setCategoriesForClient($customer, "exclusif");
                }
                
            }
       }
        
    }

    /**
     * @Route("/profile/findAllClients", name = "findAllClients")
     */
    public function findAllClients(Request $request)
    {
        $response = new Response();
        $clients = $this->repoClient->findAllForVue();
        $result = json_encode($clients);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent($result);
        return $response;
        
    }

    /**
     * @Route("/profile/findClientByHisName", name = "findClientByHisName")
     */
    public function findClientByHisName(Request $request)
    {
        $response = new Response();
        $data = json_decode($request->getContent());
        $nom = $data->nom;
        $clients = $this->repoClient->selectDistinc($nom);
        $result = json_encode($clients);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent($result);
        return $response;
        
    }

    /**
     * @Route("/admin/suprimer_client", name = "delete_client")
     */
    public function delete_client(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $visit_id = $request->get('visit_id');

            $visit = $this->repoVisit->find($visit_id);
            $manager->remove($visit);
            $manager->flush();

            $data = json_encode("deleted");
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/admin/check_client", name = "check_client")
     */
    public function check_client(SessionInterface $session, Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "hebergement";
        $pseudo_hotel = $data_session['pseudo_hotel'] ;
        if($request->isXmlHttpRequest()){
            $visit_id = $request->get('visit_id');
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $action = $request->get('action');
            //$client = $this->repoCust->find($visit_id);
            $visit = $this->repoVisit->find($visit_id);
            $client = $this->repoCust->find($visit->getCustomer()->getId());
            //dd($client->getName());
            $html = '';
            if($action == "modification"){

                $html .= '
                        
                            <input type="hidden" id = "hidden_date1" name = "date1" value = "' . $date1 . '">
                            <input type="hidden" id = "hidden_date2" name = "date2" value = "' . $date2 . '">
                            <input type="hidden" id = "hidden_id" value = "' . $client->getId() . '" name = "client_id">
                            <input type="hidden" name = "action" value = "modification">
                            <div class="form-group">
                                <label for="">Nom : </label>
                                <input type="text" id="modif_nom_client" class="form-control" value="'. $client->getName() .'">
                            </div>
                            <div class="form-group">
                                <label for="">Prénom : </label>
                                <input type="text" id="modif_prenom_client" class="form-control" value="'. $client->getLastName() .'">
                            </div>
                            <div class="mail_phone">
                                <div class="form-group">
                                    <label for="">Email : </label>
                                    <input type="text" id="modif_email_client" class="form-control" value="'. $client->getEmail() .'">
                                </div>
                                <div class="form-group">
                                    <label for="">Téléphone : </label>
                                    <input type="text" id="modif_telephone_client" class="form-control" value="'. $client->getTelephone() .'">
                                </div>
                            </div>
                            <div class="form-group modal_flex">
                                <div>
                                    <label for="">Check in : </label>
                                    <input type="date" id="modif_date_arrivee" class="input_date_checkin" value = "' . $visit->getCheckin()->format("Y-m-d") . '">
                                    <label for="" >Check out : </label>
                                    <input type="date" id="modif_date_depart" value = "' . $visit->getCheckout()->format("Y-m-d") . '">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Provenance : </label>
                                <select name="" id="modif_provenance_client">
                                    <option value="OTA" '. ($visit->getProvenance() == "OTA" ? "selected" : "" ) . '>OTA</option>
                                    <option value="DIRECT" '. ($visit->getProvenance() == "DIRECT" ? "selected" : "" ) . '>DIRECT</option>
                                    <option value="CORPO" '. ($visit->getProvenance() == "CORPO" ? "selected" : "" ) . '>CORPO</option>
                                    <option value="TOA" '. ($visit->getProvenance() == "TOA" ? "selected" : "" ) . '>TOA</option>
                                </select>
                            </div>';

                            if($visit->getProvenance() == "TOA"){
                                $html .= '
                                    <div class="modal_source_init" id="modal_source_TOA_init">
                                        <label for="">Source : </label>
                                        <input type="text" placeholder="ex Aventour" value="'. $visit->getSource() .'" class="form-control">
                                    </div>
                                ';
                            }

                            if($visit->getProvenance() == "CORPO"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_CORPO_init">
                                        <label for="">Source : </label>
                                        <input type="text" placeholder value="'. $visit->getSource() .'" ="ex Nom de la société" class="form-control">
                                    </div>
                                ';
                            }

                            if($visit->getProvenance() == "OTA"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_OTA_init">
                                        <label for="">Source : </label>
                                        <select name="">
                                            <option value="Booking" '. ($visit->getSource() == "Booking" ? "selected" : "" ) . '>Booking</option>
                                            <option value="Expedia" '. ($visit->getSource() == "Expedia" ? "selected" : "" ) . '>Expedia</option>
                                            <option value="Hotelbeds" '. ($visit->getSource() == "Hotelbeds" ? "selected" : "" ) . '>Hotelbeds</option>
                                        </select>
                                    </div>
                                ';
                            }

                            if($visit->getProvenance() == "DIRECT"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_DIRECT_init">
                                        <label for="">Source : </label>
                                        <select name="" >
                                            <option value="Email" '. ($visit->getSource() == "Email" ? "selected" : "" ) . '>Email</option>
                                            <option value="Téléphone" '. ($visit->getSource() == "Téléphone" ? "selected" : "" ) . '>Téléphone</option>
                                            <option value="Site web" '. ($visit->getSource() == "Site web" ? "selected" : "" ) . '>Site web</option>
                                            <option value="Comptoir" '. ($visit->getSource() == "Comptoir" ? "selected" : "" ) . '>Comptoir</option>
                                        </select>
                                    </div>
                                ';
                            }

                            $html .='
                            <div class=" modal_source" id="modal_source_TOA">
                                <label for="">Source : </label>
                                <input type="text" placeholder="ex Aventour" class="form-control">
                            </div>
                            <div class=" modal_source" id="modal_source_CORPO">
                                <label for="">Source : </label>
                                <input type="text" placeholder ="ex Nom de la société" class="form-control">
                            </div>
                            <div class=" modal_source" id="modal_source_OTA">
                                <label for="">Source : </label>
                                <select name="">
                                    <option value="Booking">Booking</option>
                                    <option value="Expedia">Expedia</option>
                                    <option value="Hotelbeds">Hotelbeds</option>
                                </select>
                            </div>
                            <div class=" modal_source" id="modal_source_DIRECT">
                                <label for="">Source : </label>
                                <select name="" >
                                    <option value="Email">Email</option>
                                    <option value="Téléphone">Téléphone</option>
                                    <option value="Site web">Site web</option>
                                    <option value="Comptoir">Comptoir</option>
                                </select>
                            </div>

                            <div class="form-group div_flex">
                                <label for="nbr_nuit" class="label_nbr_chambre">Nombre de chambre :</label>
                                <input type="number" id="modif_nbr_chambre_client" value="'. $visit->getNbrChambre() .'"> 
                                <label for="prix_total" class="label_prix_total">Prix total :</label>
                                <input type="text" id="modif_prix_total_client" class="input_nombre" value="'. $visit->getMontant() .'">
                                <span class="span__ar">Ar</span>
                            </div>

                            <div class=" list_btn">
                                <button type = "submit" class="btn btn-warning" id="a_modal_modif_client" data-change-source-init="non" data-id="'. $visit->getId() .'">
                                    <span>Enregistrer</span>
                                </button>
                                <button class="btn" data-dismiss="modal">
                                    <span>Annuler</span>
                                </button>
                            </div>
                        
                    ';
            }
            else{
                $html .= '
                        <form action="/profile/' . $pseudo_hotel . '/hebergement" method = "POST" id = "form_modal_delete">
                            <input type="hidden" id = "hidden_date1" name = "date1" value = "' . $date1 . '">
                            <input type="hidden" id = "hidden_date2" name = "date2" value = "' . $date2 . '">
                            <input type="hidden" id = "hidden_id" value = "' . $visit->getId() . '" name = "client_id">
                            <input type="hidden" name = "action" value = "suppression">
                            <div class="form-group">
                            <button type = "submit" class="form-control btn btn-warning"><span>Supprimer</span></button>
                            <button type = "buton" class="form-control btn btn-default" data-dismiss = "modal"><span>Annuler</span></button>
                            </div>
                        </form>';
            }

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
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
            if($id == ""){
                $data = json_encode("ok");
                $response->headers->set('Content-Type', 'application/json');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->setContent($data);
                return $response;
            }          
            $nom = $request->get('nom');
            $prenom = $request->get('prenom');
            
            $nbr_chambre = $request->get('nbr_chambre');
            // $tarif = $request->get('tarif');
            $provenance = $request->get('provenance');
            $email = $request->get('email');
            $telephone = $request->get('telephone');
            $source = $request->get('source');
            $prix_total = $request->get('prix_total');
            $date_arrivee = $request->get('date_arrivee');
            $date_depart = $request->get('date_depart');
            $date_arrivee = date_create($date_arrivee);
            $date_depart = date_create($date_depart);
            $diff = $date_arrivee->diff($date_depart);
            $days = $diff->d;
            // on pick le client
            $visit = $this->repoVisit->find($id);
            $client = $this->repoCust->find($visit->getCustomer()->getId());
            $client->setName($nom);
            $client->setLastName($prenom);
            
            $visit->setProvenance($provenance);
            $client->setEmail($email);
            $client->setTelephone($telephone);
            $visit->setSource($source);
            $visit->setNbrChambre($nbr_chambre);
            $visit->setMontant(str_replace(" ", "", $prix_total));
            $visit->setCheckin($date_arrivee);
            $visit->setCheckout($date_depart);
            $visit->setNbrNuitee($days);
            $manager->persist($client);
            $manager->persist($visit);
            $manager->flush();

            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/edit_client_by_fid", name="edit_client_by_fid")
     */
    public function edit_client_by_fid(Request $request, EntityManagerInterface $manager):Response
    {
        $response = new Response;
        $data = json_decode($request->getContent());
        $nom = $data->nom;
        $prenom = $data->prenom;
        $email = $data->email;
        $telephone = $data->telephone;
        $client_by_mail = $this->repoClient->findBy([
            "email" => $email
        ]);
        
        $client_by_telephone = $this->repoClient->findBy([
            "telephone" => $telephone
        ]);
        //dd($client_by_telephone);
        if($client_by_mail){
            foreach($client_by_mail as $client){
                $client->setNom($nom);
                $client->setPrenom($prenom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $manager->flush();
            }
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);

        }
        if($client_by_telephone){
            foreach($client_by_telephone as $client){
                $client->setNom($nom);
                $client->setPrenom($prenom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $manager->flush();
            }
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setContent($data);

        }

        return $response;
        
    }
    
}
