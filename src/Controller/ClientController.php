<?php

namespace App\Controller;

use App\Entity\Client;
use App\Services\Services;
use App\Entity\Fidelisation;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
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
    private $manager;
    private $startFidelisation; // date de debut de la catégorisation

    public function __construct(ClientRepository $repoClient, FidelisationRepository $repoFid, EntityManagerInterface $manager)
    {
        $this->startFidelisation = date_create("2021-01-01"); 
        $this->repoClient = $repoClient;
        $this->manager = $manager;
        $this->repoFid = $repoFid;
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
    public function listing_current_client(Services $services, Request $request, ClientRepository $repoClient, HotelRepository $repoHotel)
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
                $tab = $repoClient->findBy(['hotel' => $hotel], ["id" => "DESC"]);
                
                foreach ($tab as $client) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $sa_da = $client->getDateArrivee();
                    $sa_dd = $client->getDateDepart();
                    if ($today <= $sa_dd && $today >= $sa_da
                    ) {
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
                
                $tab = $repoClient->findBy(['hotel' => $hotel], ["id" => "DESC"]);
               
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
            }
            
            $t = [];
            foreach ($tab_aff as $item) {

                array_push($t, ['<div>' . $item->getNom() . '</div><div>' . $item->getPrenom() . '</div><div><span>Contact : </span>' 
                . $item->afficheContact() . '</div>', $item->getDateArrivee()->format('d-m-Y'), $item->getDateDepart()->format('d-m-Y'), $item->getProvenance(), $item->getSource(), $item->getDureeSejour(), $item->getNbrChambre(), '<span class="montant">' 
                .$item->getPrixTotal().'</span>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_diso" data-id = "' 
                . $item->getId() . '" class="btn btn_client_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a><a href="#" data-toggle="modal" data-target="#modal_form_confirme" data-id = "' 
                . $item->getId() . '" class="btn btn_client_suppr btn-danger btn-xs"><span class="fa fa-trash-o"></span></a></div>']);
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
        Request $request,
        $pseudo_hotel)
    {
        $response = new Response();
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
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
            $client = new Client();
            $t = ['0','1','2', '3', '4', '5', '6', '7', '8', '9'];
            // start client insertiion
            if(!empty($date_depart) && !empty($date_arrivee)){
                $date_arrivee = date_create($date_arrivee);
                $date_depart = date_create($date_depart);
                $diff = $date_arrivee->diff($date_depart);
                $days = $diff->days;
                //dd($days);
            }
            
            if(!empty($email) && $email != "0"){
                if(filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array($email[0], $t)){
                    if(!empty($nom_client) 
                            && !empty($date_depart) 
                            && !empty($date_arrivee)
                            && !empty($prix_total) 
                            && !empty($nbr_chambre)
                            && !empty($source)
                    ){
                        
                        $client->setNom($nom_client);
                        $client->setPrenom($prenom_client);
                        $client->setDateArrivee($date_arrivee);
                        $client->setTarif($tarif_client);
                        $client->setDateDepart($date_depart);
                        $client->setDureeSejour($days);
                        $client->setSource($source);
                        $client->setEmail($email);
                        
                        $client->setTelephone($telephone);
                        $client->setProvenance($provenance);
                        $client->setNbrChambre($nbr_chambre);
                        $client->setPrixTotal(str_replace(" ", "", $prix_total));
                        $client->setCreatedAt($createdAt);
                       
                        $hotel->addClient($client);
                        // fidelisation 
        
                        $this->manager->persist($client);
                        $this->manager->persist($hotel);
                        $this->manager->flush();
                        $this->categorisation($tab_identifiant);
                        $data = json_encode("ok");  
                    }
                    else{
                        $data = json_encode("Veuiller renseigner au moins les champs Nom, Check in, check out, Source, Nbr chambres, Prix total"); 
                    }
                }else{
                    $data = json_encode("Veuiller entrer un adresse email valide"); 
                }
            }else if(empty($email) || $email == "0"){
                if(!empty($nom_client) 
                        && !empty($date_depart) 
                        && !empty($date_arrivee)
                        && !empty($prix_total) 
                        && !empty($nbr_chambre)
                        && !empty($telephone)
                        && !empty($source)
                ){
                    
                    $client->setNom($nom_client);
                    $client->setPrenom($prenom_client);
                    $client->setDateArrivee($date_arrivee);
                    $client->setTarif($tarif_client);
                    $client->setDateDepart($date_depart);
                    $client->setDureeSejour($days);
                    $client->setSource($source);
                    $client->setTelephone($telephone);
                    $client->setProvenance($provenance);
                    $client->setNbrChambre($nbr_chambre);
                    $client->setPrixTotal(str_replace(" ", "", $prix_total));
                    $client->setCreatedAt($createdAt);
                    $hotel->addClient($client);
                    // fidelisation 
                    $this->manager->persist($client);
                    $this->manager->persist($hotel);
                    $this->manager->flush();
                    $this->categorisation($tab_identifiant);
                    $data = json_encode("ok");  
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

    public function setCategoriesForClients($clients, $nameOfFid){
        $fidelisation = $this->repoFid->findOneBy(['nom'=>$nameOfFid]);
        foreach($clients as $item){
            $item->setFidelisation($fidelisation);
        }
        $this->manager->flush();
    }

    /**
     * catégorisation du client inseré
     * 
    */
    public function categorisation($tab_identifiant) :void
    {

        $clients = $this->repoClient->searchClientsByTabIdentifiant($tab_identifiant, $this->startFidelisation);
        $fids = $this->repoFid->findAll(); // par défaut findAll affiche un resultat ascendant d'id
        
        //dd($clients);
        $client = ($clients != null ? end($clients) : null );
        // la dernière date d'insertion de ce client
       if($client){
            $lastCreatedAt = $client->getCreatedAt();

            $diff = date_diff($lastCreatedAt, new \DateTime())->y;
            if($diff >= 1){
                $this->setCategoriesForClients($clients, "cardex");
            }
            else if($diff < 1){
                $ca = 0;
                $nuitee = 0;
                foreach($clients as $item){
                    $ca = $ca + $item->getPrixTotal();
                    $nuitee = $nuitee + $item->getDureeSejour();
                }
                if( $nuitee >= 0 &&  $nuitee <= $fids[0]->getLimiteNuite()){
                    $this->setCategoriesForClients($clients, "cardex");
                }
                if( $nuitee >= (intval($fids[0]->getLimiteNuite()) + 1) &&  $nuitee <= $fids[1]->getLimiteNuite()){
                    $this->setCategoriesForClients($clients, "preferentiel");
                }
                if( $nuitee >= (intval($fids[1]->getLimiteNuite()) + 1) &&  $nuitee <= $fids[2]->getLimiteNuite()){
                    $this->setCategoriesForClients($clients, "privilege");
                }
                if( $nuitee >= (intval($fids[2]->getLimiteNuite()) + 1)){ 
                    $this->setCategoriesForClients($clients, "exclusif");
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
            $client_id = $request->get('client_id');

            $client = $repoClient->find($client_id);
            $manager->remove($client);
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
            $client_id = $request->get('client_id');
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            $action = $request->get('action');
            $client = $repoClient->find($client_id);
            $html = '';
            if($action == "modification"){

                $html .= '
                        
                            <input type="hidden" id = "hidden_date1" name = "date1" value = "' . $date1 . '">
                            <input type="hidden" id = "hidden_date2" name = "date2" value = "' . $date2 . '">
                            <input type="hidden" id = "hidden_id" value = "' . $client->getId() . '" name = "client_id">
                            <input type="hidden" name = "action" value = "modification">
                            <div class="form-group">
                                <label for="">Nom : </label>
                                <input type="text" id="modif_nom_client" class="form-control" value="'. $client->getNom() .'">
                            </div>
                            <div class="form-group">
                                <label for="">Prénom : </label>
                                <input type="text" id="modif_prenom_client" class="form-control" value="'. $client->getPrenom() .'">
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
                                    <input type="date" id="modif_date_arrivee" class="input_date_checkin" value = "' . $client->getDateArrivee()->format("Y-m-d") . '">
                                    <label for="" >Check out : </label>
                                    <input type="date" id="modif_date_depart" value = "' . $client->getDateDepart()->format("Y-m-d") . '">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Provenance : </label>
                                <select name="" id="modif_provenance_client">
                                    <option value="OTA" '. ($client->getProvenance() == "OTA" ? "selected" : "" ) . '>OTA</option>
                                    <option value="DIRECT" '. ($client->getProvenance() == "DIRECT" ? "selected" : "" ) . '>DIRECT</option>
                                    <option value="CORPO" '. ($client->getProvenance() == "CORPO" ? "selected" : "" ) . '>CORPO</option>
                                    <option value="TOA" '. ($client->getProvenance() == "TOA" ? "selected" : "" ) . '>TOA</option>
                                </select>
                            </div>';

                            if($client->getProvenance() == "TOA"){
                                $html .= '
                                    <div class="modal_source_init" id="modal_source_TOA_init">
                                        <label for="">Source : </label>
                                        <input type="text" placeholder="ex Aventour" value="'. $client->getSource() .'" class="form-control">
                                    </div>
                                ';
                            }

                            if($client->getProvenance() == "CORPO"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_CORPO_init">
                                        <label for="">Source : </label>
                                        <input type="text" placeholder value="'. $client->getSource() .'" ="ex Nom de la société" class="form-control">
                                    </div>
                                ';
                            }

                            if($client->getProvenance() == "OTA"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_OTA_init">
                                        <label for="">Source : </label>
                                        <select name="">
                                            <option value="Booking" '. ($client->getSource() == "Booking" ? "selected" : "" ) . '>Booking</option>
                                            <option value="Expedia" '. ($client->getSource() == "Expedia" ? "selected" : "" ) . '>Expedia</option>
                                            <option value="Hotelbeds" '. ($client->getSource() == "Hotelbeds" ? "selected" : "" ) . '>Hotelbeds</option>
                                        </select>
                                    </div>
                                ';
                            }

                            if($client->getProvenance() == "DIRECT"){
                                $html .= '
                                    <div class=" modal_source_init" id="modal_source_DIRECT_init">
                                        <label for="">Source : </label>
                                        <select name="" >
                                            <option value="Email" '. ($client->getSource() == "Email" ? "selected" : "" ) . '>Email</option>
                                            <option value="Téléphone" '. ($client->getSource() == "Téléphone" ? "selected" : "" ) . '>Téléphone</option>
                                            <option value="Site web" '. ($client->getSource() == "Site web" ? "selected" : "" ) . '>Site web</option>
                                            <option value="Comptoir" '. ($client->getSource() == "Comptoir" ? "selected" : "" ) . '>Comptoir</option>
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
                                <input type="number" id="modif_nbr_chambre_client" value="'. $client->getNbrChambre() .'"> 
                                <label for="prix_total" class="label_prix_total">Prix total :</label>
                                <input type="text" id="modif_prix_total_client" class="input_nombre" value="'. $client->getPrixTotal() .'">
                                <span class="span__ar">Ar</span>
                            </div>

                            <div class=" list_btn">
                                <button type = "submit" class="btn btn-warning" id="a_modal_modif_client" data-change-source-init="non" data-id="'. $client->getId() .'">
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
                            <input type="hidden" id = "hidden_id" value = "' . $client->getId() . '" name = "client_id">
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
            $client = $repoClient->find($id);
            $client->setNom($nom);
            $client->setPrenom($prenom);
            
            $client->setProvenance($provenance);
            $client->setEmail($email);
            $client->setTelephone($telephone);
            $client->setSource($source);
            $client->setNbrChambre($nbr_chambre);
            $client->setPrixTotal(str_replace(" ", "", $prix_total));
            $client->setDateArrivee($date_arrivee);
            $client->setDateDepart($date_depart);
            $client->setDureeSejour($days);
            $manager->persist($client);
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
