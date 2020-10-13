<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DonneeDuJour;
use App\Form\DonneeDuJourType;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DonneeDuJourRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PageController extends AbstractController
{

    /**
     * @Route("/", name="first_page")
     */
    public function first_page(Request $request)
    {
        return $this->redirectToRoute("app_login");
    }

   
    /**
     * @Route("/profile/{pseudo_hotel}/setting", name="setting")
     */
    public function setting(UserRepository $repoUser, Request $request, EntityManagerInterface $manager, SessionInterface $session, $pseudo_hotel)
    {
        $tab_user = $repoUser->findAll();
        $data_session = $session->get('hotel');
        $data_session['pseudo_hote'] = $pseudo_hotel;
        $data_session['current_page'] = "setting";
        return $this->render('/page/setting.html.twig', [
            "liste_user" => $tab_user,
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']

        ]);
    }
    /**
     * @Route("/profile/{pseudo_hotel}/crj", name="crj")
     */
    public function crj(SessionInterface $session,$pseudo_hotel, HotelRepository $repoHotel, DonneeDuJourRepository $repoDoneeDJ)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "crj";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $current_id_hotel = $l_hotel->getId();
        $donneeDJs = $repoDoneeDJ->findAll();
        $tab = [];
        foreach($donneeDJs as $item){
            $son_hotel = $item->getHotel();
            $son_id_hotel = $son_hotel->getId();
            if($son_id_hotel == $current_id_hotel){
                array_push($tab, $item);
            }
        }
        return $this->render('page/crj.html.twig', [
            "items" => $tab,
            "id" => "li__compte_rendu",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/hebergement", name="hebergement")
     */
    public function hebergement($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "hebergement";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        if ($request->isXmlHttpRequest()) {

            $nom_client = $request->get('nom_client');
            $prenom_client = (empty($request->get('prenom_client'))) ? "" : $request->get('prenom_client');
            $date_arrivee = $request->get('date_arrivee');
            $date_depart = $request->get('date_depart');
            $date_arrivee = date_create($date_arrivee);
            $date_depart = date_create($date_depart);
            $diff = $date_arrivee->diff($date_depart);
            $days = $diff->d;
           
            // condition 
            if(!empty($nom_client) && !empty($date_depart) && !empty($date_arrivee)){
                $client = new Client();
                $client->setNom($nom_client);
                $client->setPrenom($prenom_client);
                $client->setDateArrivee($date_arrivee);
                $client->setDateDepart($date_depart);
                $client->setDureeSejour($days);
                $client->setCreatedAt(new \DateTime());
                $hotel->addClient($client);
                $manager->persist($client);
                $manager->persist($hotel);
                $manager->flush();
                $data = json_encode("ok");
               
            }
            else{
                $data = json_encode("Veuiller remplir ces formulaires"); 
            }

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;

        }

        /** préparation des input des filtres */
        // les année existant dans les donnée de jour pour l'hotel en cours
        $all_ddj = $repoDoneeDJ->findAll();
        $current_hotel_ddj = [];
        foreach($all_ddj as $d){
            $son_hotel = $d->getHotel()->getPseudo();
            if($son_hotel == $pseudo_hotel){
                array_push($current_hotel_ddj, $d);
            }
        }
        //dd($current_hotel_ddj);
        $tab_annee = [];
        $tab_sans_doublant = [];
        foreach($current_hotel_ddj as $c){
            $son_created_at = $c->getCreatedAt();
            $annee = $son_created_at->format('Y');
            array_push($tab_annee, $annee); 
        }
        array_push($tab_sans_doublant, $tab_annee[0]);
        for($i = 0; $i < count($tab_annee); $i++){
            
            if(!in_array($tab_annee[$i], $tab_sans_doublant)){
                array_push($tab_sans_doublant, $tab_annee[$i]);
            }
            
        }
        //dd($tab_sans_doublant);
        // on affiche le donné dans chart
        // selection de tous les ddj pour heb

        // les heb_to pour chaque mois

        $heb_to_jan = 0;
        $heb_to_fev = 0;
        $heb_to_mars = 0;
        $heb_to_avr = 0;
        $heb_to_mai = 0;
        $heb_to_juin = 0;
        $heb_to_juil = 0;
        $heb_to_aou = 0;
        $heb_to_sep = 0;
        $heb_to_oct = 0;
        $heb_to_nov = 0;
        $heb_to_dec = 0;

        // les heb_to pour chaque mois

        $heb_ca_jan = 0;
        $heb_ca_fev = 0;
        $heb_ca_mars = 0;
        $heb_ca_avr = 0;
        $heb_ca_mai = 0;
        $heb_ca_juin = 0;
        $heb_ca_juil = 0;
        $heb_ca_aou = 0;
        $heb_ca_sep = 0;
        $heb_ca_oct = 0;
        $heb_ca_nov = 0;
        $heb_ca_dec = 0;

        // effectif pour la moyen 

        $e_jan = 0;
        $e_fev = 0;
        $e_mars = 0;
        $e_avr = 0;
        $e_mai = 0;
        $e_juin = 0;
        $e_juil = 0;
        $e_aou = 0;
        $e_sep = 0;
        $e_oct = 0;
        $e_nov = 0;
        $e_dec = 0;

        // effectif pour la moyen 

        $eca_jan = 0;
        $eca_fev = 0;
        $eca_mars = 0;
        $eca_avr = 0;
        $eca_mai = 0;
        $eca_juin = 0;
        $eca_juil = 0;
        $eca_aou = 0;
        $eca_sep = 0;
        $eca_oct = 0;
        $eca_nov = 0;
        $eca_dec = 0;

        
        
        $all_ddj = $repoDoneeDJ->findAll();
        $annee_actuel = new \DateTime() ;
        $annee_actuel = $annee_actuel->format("Y");
        //dd($annee_actuel);
        foreach($all_ddj as $ddj){
            $son_createdAt = $ddj->getCreatedAt();
            $son_mois_ca = $son_createdAt->format("m");
            $son_annee_ca = $son_createdAt->format("Y");
            if($son_annee_ca == $annee_actuel){
                if ($son_mois_ca == "01") {
                    $e_jan++;
                    $heb_to_jan += $ddj->getHebTo();
                }
                if ($son_mois_ca == "02") {
                    $e_fev++;
                    $heb_to_fev += $ddj->getHebTo();
                }
                if ($son_mois_ca == "03") {
                    $e_mars++;
                    $heb_to_mars += $ddj->getHebTo();
                }
                if ($son_mois_ca == "04") {
                    $e_avr++;
                    $heb_to_avr += $ddj->getHebTo();
                }
                if ($son_mois_ca == "05") {
                    $e_mai++;
                    $heb_to_mai += $ddj->getHebTo();
                }
                if ($son_mois_ca == "06") {
                    $e_juin++;
                    $heb_to_juin += $ddj->getHebTo();
                }
                if ($son_mois_ca == "07") {
                    $e_juil++;
                    $heb_to_juil += $ddj->getHebTo();
                }
                if ($son_mois_ca == "08") {
                    $e_aou++;
                    $heb_to_aou += $ddj->getHebTo();
                }
                if ($son_mois_ca == "09") {
                    $e_sep++;
                    $heb_to_sep += $ddj->getHebTo();
                }
                if ($son_mois_ca == "10") {
                    $e_oct++;
                    $heb_to_oct += $ddj->getHebTo();
                }
                if ($son_mois_ca == "11") {
                    $e_nov++;
                    $heb_to_nov += $ddj->getHebTo();
                }
                if ($son_mois_ca == "12") {
                    $e_dec++;
                    $heb_to_dec += $ddj->getHebTo();
                }
            }
            
        }

        foreach ($all_ddj as $ddj) {
            $son_createdAt = $ddj->getCreatedAt();
            $son_mois_ca = $son_createdAt->format("m");
            $son_annee_ca = $son_createdAt->format("Y");
            if ($son_annee_ca == $annee_actuel) {
                if ($son_mois_ca == "01") {
                    $eca_jan++;
                    $heb_ca_jan += $ddj->getHebCa();
                }
                if ($son_mois_ca == "02") {
                    $eca_fev++;
                    $heb_ca_fev += $ddj->getHebCa();
                }
                if ($son_mois_ca == "03") {
                    $eca_mars++;
                    $heb_ca_mars += $ddj->getHebCa();
                }
                if ($son_mois_ca == "04") {
                    $eca_avr++;
                    $heb_ca_avr += $ddj->getHebCa();
                }
                if ($son_mois_ca == "05") {
                    $eca_mai++;
                    $heb_ca_mai += $ddj->getHebCa();
                }
                if ($son_mois_ca == "06") {
                    $eca_juin++;
                    $heb_ca_juin += $ddj->getHebCa();
                }
                if ($son_mois_ca == "07") {
                    $eca_juil++;
                    $heb_ca_juil += $ddj->getHebCa();
                }
                if ($son_mois_ca == "08") {
                    $eca_aou++;
                    $heb_ca_aou += $ddj->getHebCa();
                }
                if ($son_mois_ca == "09") {
                    $eca_sep++;
                    $heb_ca_sep += $ddj->getHebCa();
                }
                if ($son_mois_ca == "10") {
                    $eca_oct++;
                    $heb_ca_oct += $ddj->getHebCa();
                }
                if ($son_mois_ca == "11") {
                    $eca_nov++;
                    $heb_ca_nov += $ddj->getHebCa();
                }
                if ($son_mois_ca == "12") {
                    $eca_dec++;
                    $heb_ca_dec += $ddj->getHebCa();
                }
            }
        }
        $tab_heb_to = [$heb_to_jan, $heb_to_fev, $heb_to_mars, $heb_to_avr, $heb_to_mai, $heb_to_juin, $heb_to_juil, $heb_to_aou, $heb_to_sep, $heb_to_oct, $heb_to_nov, $heb_to_dec];
        $tab_e = [$e_jan, $e_fev, $e_mars, $e_avr, $e_mai, $e_juin, $e_juil, $e_aou, $e_sep, $e_oct, $e_nov, $e_dec];
        for($i = 0; $i < count($tab_e); $i++){
            if($tab_e[$i] == 0){
                $tab_e[$i] = 1;
            }
            $tab_heb_to[$i] = number_format(($tab_heb_to[$i] / $tab_e[$i]), 2);
        }

        $tab_heb_ca = [$heb_ca_jan, $heb_ca_fev, $heb_ca_mars, $heb_ca_avr, $heb_ca_mai, $heb_ca_juin, $heb_ca_juil, $heb_ca_aou, $heb_ca_sep, $heb_ca_oct, $heb_ca_nov, $heb_ca_dec];
        $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
        for ($i = 0; $i < count($tab_e); $i++) {
            if ($tab_eca[$i] == 0) {
                $tab_eca[$i] = 1;
            }
            $tab_heb_ca[$i] = number_format(($tab_heb_ca[$i] / $tab_eca[$i]), 2);
        }





        //dd($tab_heb_to);
        $tab_labels = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sept",
            "Oct",
            "Nov",
            "Dec"
        ];
       
        return $this->render('page/hebergement.html.twig', [
            "id" => "li__hebergement",
            "tab_annee" => $tab_sans_doublant,
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page'],
            "tab_heb_to" => $tab_heb_to,
            "tab_heb_ca" => $tab_heb_ca,
            "tab_labels" => $tab_labels,
            "type_affichage" => "annee",
        ]);
    }

    /**
     * @Route("/admin/filtre/graph/heb_to/{pseudo_hotel}", name = "filtre_graph_heb_to")
     */
    public function filtre_graph_heb_to($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
           
            $donnee = $request->get('data');
            $donnee_explode = explode("-",$donnee);
            if($donnee_explode[0] != 'tous_les_mois'){
                
                $all_ddj = $repoDoneeDJ->findAll();

                // les var pour les heb_to

                $tab_jour_heb_to = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    if ($donnee == $son_mois_createdAt) {
                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_heb_to[$num] = $d->getHebTo();
                    }
                }


                $data = json_encode($tab_jour_heb_to);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
            else{
                // hafa
                $all_ddj = $repoDoneeDJ->findAll();
                // les heb_to pour chaque mois

                $heb_to_jan = 0;
                $heb_to_fev = 0;
                $heb_to_mars = 0;
                $heb_to_avr = 0;
                $heb_to_mai = 0;
                $heb_to_juin = 0;
                $heb_to_juil = 0;
                $heb_to_aou = 0;
                $heb_to_sep = 0;
                $heb_to_oct = 0;
                $heb_to_nov = 0;
                $heb_to_dec = 0;

                // effectif pour la moyen 

                $e_jan = 0;
                $e_fev = 0;
                $e_mars = 0;
                $e_avr = 0;
                $e_mai = 0;
                $e_juin = 0;
                $e_juil = 0;
                $e_aou = 0;
                $e_sep = 0;
                $e_oct = 0;
                $e_nov = 0;
                $e_dec = 0;
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $e_jan++;
                            $heb_to_jan += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "02") {
                            $e_fev++;
                            $heb_to_fev += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "03") {
                            $e_mars++;
                            $heb_to_mars += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "04") {
                            $e_avr++;
                            $heb_to_avr += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "05") {
                            $e_mai++;
                            $heb_to_mai += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "06") {
                            $e_juin++;
                            $heb_to_juin += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "07") {
                            $e_juil++;
                            $heb_to_juil += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "08") {
                            $e_aou++;
                            $heb_to_aou += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "09") {
                            $e_sep++;
                            $heb_to_sep += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "10") {
                            $e_oct++;
                            $heb_to_oct += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "11") {
                            $e_nov++;
                            $heb_to_nov += $ddj->getHebTo();
                        }
                        if ($son_mois_ca == "12") {
                            $e_dec++;
                            $heb_to_dec += $ddj->getHebTo();
                        }
                    }
            
                }
                $tab_heb_to = [$heb_to_jan, $heb_to_fev, $heb_to_mars, $heb_to_avr, $heb_to_mai, $heb_to_juin, $heb_to_juil, $heb_to_aou, $heb_to_sep, $heb_to_oct, $heb_to_nov, $heb_to_dec];
                $tab_e = [$e_jan, $e_fev, $e_mars, $e_avr, $e_mai, $e_juin, $e_juil, $e_aou, $e_sep, $e_oct, $e_nov, $e_dec];
                for ($i = 0; $i < count($tab_e); $i++) {
                    if ($tab_e[$i] == 0) {
                        $tab_e[$i] = 1;
                    }
                    $tab_heb_to[$i] = number_format(($tab_heb_to[$i] / $tab_e[$i]), 2);
                }


                $data = json_encode($tab_heb_to);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
                
            }

           
        }

        $donnee = '05-2020';
        $all_ddj = $repoDoneeDJ->findAll();

        // les var pour les heb_to

        $tab_jour_heb_to = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
       
        $num = 0;
        foreach ($all_ddj as $d) {
            $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
            if ($donnee == $son_mois_createdAt) {
                $son_num_jour = $d->getCreatedAt()->format('d');
                $num = intval($son_num_jour) - 1;
                $tab_jour_heb_to[$num] = $d->getHebTo();
            }
        }
        dd($tab_jour_heb_to);
    }


    /**
     * @Route("/admin/filtre/graph/heb_ca/{pseudo_hotel}", name = "filtre_graph_heb_ca")
     */
    public function filtre_graph_heb_ca($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            if ($donnee_explode[0] != 'tous_les_mois') {

                $all_ddj = $repoDoneeDJ->findAll();

                // les var pour les heb_to

                $tab_jour_heb_ca = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    if ($donnee == $son_mois_createdAt) {
                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_heb_ca[$num] = $d->getHebTo();
                    }
                }



                $data = json_encode($tab_jour_heb_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
                // hafa
                $all_ddj = $repoDoneeDJ->findAll();
                // les heb_to pour chaque mois

                $heb_ca_jan = 0;
                $heb_ca_fev = 0;
                $heb_ca_mars = 0;
                $heb_ca_avr = 0;
                $heb_ca_mai = 0;
                $heb_ca_juin = 0;
                $heb_ca_juil = 0;
                $heb_ca_aou = 0;
                $heb_ca_sep = 0;
                $heb_ca_oct = 0;
                $heb_ca_nov = 0;
                $heb_ca_dec = 0;

                // effectif pour la moyen 

                $e_jan = 0;
                $e_fev = 0;
                $e_mars = 0;
                $e_avr = 0;
                $e_mai = 0;
                $e_juin = 0;
                $e_juil = 0;
                $e_aou = 0;
                $e_sep = 0;
                $e_oct = 0;
                $e_nov = 0;
                $e_dec = 0;
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $e_jan++;
                            $heb_ca_jan += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "02") {
                            $e_fev++;
                            $heb_ca_fev += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "03") {
                            $e_mars++;
                            $heb_ca_mars += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "04") {
                            $e_avr++;
                            $heb_ca_avr += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "05") {
                            $e_mai++;
                            $heb_ca_mai += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "06") {
                            $e_juin++;
                            $heb_ca_juin += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "07") {
                            $e_juil++;
                            $heb_ca_juil += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "08") {
                            $e_aou++;
                            $heb_ca_aou += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "09") {
                            $e_sep++;
                            $heb_ca_sep += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "10") {
                            $e_oct++;
                            $heb_ca_oct += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "11") {
                            $e_nov++;
                            $heb_ca_nov += $ddj->getHebCa();
                        }
                        if ($son_mois_ca == "12") {
                            $e_dec++;
                            $heb_ca_dec += $ddj->getHebCa();
                        }
                    }
                }
                $tab_heb_ca = [$heb_ca_jan, $heb_ca_fev, $heb_ca_mars, $heb_ca_avr, $heb_ca_mai, $heb_ca_juin, $heb_ca_juil, $heb_ca_aou, $heb_ca_sep, $heb_ca_oct, $heb_ca_nov, $heb_ca_dec];
                $tab_e = [$e_jan, $e_fev, $e_mars, $e_avr, $e_mai, $e_juin, $e_juil, $e_aou, $e_sep, $e_oct, $e_nov, $e_dec];
                for ($i = 0; $i < count($tab_e); $i++) {
                    if ($tab_e[$i] == 0) {
                        $tab_e[$i] = 1;
                    }
                    $tab_heb_ca[$i] = number_format(($tab_heb_ca[$i] / $tab_e[$i]), 2);
                }


                $data = json_encode($tab_heb_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

        $donnee = '05-2020';
        $all_ddj = $repoDoneeDJ->findAll();

        // les var pour les heb_ca

        $tab_jour_heb_ca = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $num = 0;
        foreach ($all_ddj as $d) {
            $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
            if ($donnee == $son_mois_createdAt) {
                $son_num_jour = $d->getCreatedAt()->format('d');
                $num = intval($son_num_jour) - 1;
                $tab_jour_heb_ca[$num] = $d->getHebCa();
            }
        }
        dd($tab_jour_heb_ca);
    }


    /**
     * @Route("/profile/{pseudo_hotel}/restaurant", name="restaurant")
     */
    public function restaurant(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "restaurant";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/restaurant.html.twig', [
            "id" => "li__restaurant",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/spa", name="spa")
     */
    public function spa(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "spa";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/spa.html.twig', [
            "id" => "li__spa",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/fiche_hotel", name="fiche_hotel")
     */
    public function fiche_hotel(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "fiche_hotel";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/fiche_hotel.html.twig', [
            "id" => "fiche_hotel",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/donnee_jour", name="donnee_jour")
     */
    public function donnee_jour(Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {
        $ddj = new DonneeDuJour();
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "donnee_jour";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $form = $this->createForm(DonneeDuJourType::class, $ddj);
        // si le formulaire est soumis 
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ddj = $form->getData();
            $ddj->setCreatedAt(new \DateTime());
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $hotel->addDonneeDuJour($ddj);
            $manager->persist($ddj);
            $manager->persist($hotel);
            $manager->flush();
            return $this->redirectToRoute('donnee_jour', ['pseudo_hotel' => $pseudo_hotel]);
        }

        return $this->render('page/donnee_jour.html.twig', [
            "id" => "li__donnee_du_jour",
            "form" => $form->createView(),
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_hebergement", name="h_hebergement")
     */
    public function h_hebergement(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "h_hebergement";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/h_hebergement.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_restaurant", name="h_restaurant")
     */
    public function h_restaurant(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "h_restaurant";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/h_restaurant.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_spa", name="h_spa")
     */
    public function h_spa(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "h_spa";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/h_spa.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    // Route pour les tris mois

    /**
     * @Route("/profile/{pseudo_hotel}/t_hebergement", name = "t_hebergement")
     */
    public function t_hebergement(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "t_hebergement";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/tri_mois/hebergement.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/t_restaurant", name = "t_restaurant")
     */
    public function t_restaurant(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "t_restaurant";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/tri_mois/restaurant.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/t_spa", name = "t_spa")
     */
    public function t_spa(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "t_spa";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/tri_mois/spa.html.twig', [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    // navigation entre les hotels

    /**
     * @Route("/profile/royal_beach/{current_page}", name="royal_beach")
     */
    public function royal_beach($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "royal_beach";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], ['pseudo_hotel' => $data_session['pseudo_hotel']]);
    }

    /**
     * @Route("/profile/calypso/{current_page}", name="calypso")
     */
    public function calypso($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "calypso";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], ['pseudo_hotel' => $data_session['pseudo_hotel']]);
    }

    /**
     * @Route("/profile/baobab_tree/{current_page}", name="baobab_tree")
     */
    public function baobab_tree($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "baobab_tree";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], ['pseudo_hotel' => $data_session['pseudo_hotel']]);
    }

    /**
     * @Route("/profile/vanila_hotel/{current_page}", name="vanila_hotel")
     */
    public function vanila_hotel($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "vanila_hotel";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], ['pseudo_hotel' => $data_session['pseudo_hotel']]);
    }


}
