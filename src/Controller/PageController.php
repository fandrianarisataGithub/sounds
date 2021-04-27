<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\FicheHotel;
use App\Services\Services;
use App\Entity\Fournisseur;
use App\Entity\ClientUpload;
use App\Entity\DonneeDuJour;
use App\Form\DonneeDuJourType;
use App\Entity\DonneeMensuelle;
use App\Entity\DataTropicalWood;
use App\Form\DonneeMensuelleType;
use App\Form\FournisseurFileType;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use App\Repository\FicheHotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FournisseurRepository;
use App\Repository\ClientUploadRepository;
use App\Repository\DonneeDuJourRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DataTropicalWoodRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class PageController extends AbstractController
{

    private  $services;
    public function __construct(Services $services)
    {
        $this->services = $services;
    }

    /**
     * @Route("/", name="first_page")
     */
    public function first_page(Request $request)
    {
        return $this->redirectToRoute("app_login");
    }


    /**
     * @Route("/profile/setting/{pseudo_hotel}", name="setting")
     */
    public function setting(HotelRepository $repoHotel, UserRepository $repoUser, Request $request, EntityManagerInterface $manager, SessionInterface $session, $pseudo_hotel)
    {   
       
        $tab_user = $repoUser->findAll();
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $tropical_wood = null;
        if($data_session['pseudo_hotel'] == "tropical_wood"){
            $tropical_wood = true;
        }
        $data_session['current_page'] = "setting";
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
       
        if($pos == "impossible"){
            return $this->render('/page/error.html.twig');
        }
        else{
            return $this->render('/page/setting.html.twig', [
                "liste_user"        => $tab_user,
                "hotel"             => $data_session['pseudo_hotel'],
                "current_page"      => $data_session['current_page'],
                "tropical_wood"     => $tropical_wood,
            ]);
        }
    }
    /**
     * @Route("/profile/{pseudo_hotel}/crj", name="crj")
     */
    public function crj(Request $request, PaginatorInterface $paginator, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel, DonneeDuJourRepository $repoDoneeDJ)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "crj";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $current_id_hotel = $l_hotel->getId();
        $donneeDJs = $repoDoneeDJ->findAll();

        $all_ddj = $repoDoneeDJ->findAll();
        $current_hotel_ddj = [];
        foreach ($all_ddj as $d) {
            $son_hotel = $d->getHotel()->getPseudo();
            if ($son_hotel == $pseudo_hotel) {
                array_push($current_hotel_ddj, $d);
            }
        }
        //dd($current_hotel_ddj);
        $tab_annee = [];
        $tab_sans_doublant = [];
        foreach ($current_hotel_ddj as $c) {
            $son_created_at = $c->getCreatedAt();
            $annee = $son_created_at->format('Y');
            array_push($tab_annee, $annee);
        }
        if (count($tab_annee) > 0) {
            array_push($tab_sans_doublant, $tab_annee[0]);
        }
        for ($i = 0; $i < count($tab_annee); $i++) {

            if (!in_array($tab_annee[$i], $tab_sans_doublant)) {
                array_push($tab_sans_doublant, $tab_annee[$i]);
            }
        }


        /** si il ya des requetes */

        if($request->request->count() > 0){
            $action = $request->request->get('action');
            //dd($action);
            if($action == "tri_annee"){
                $query = $repoDoneeDJ->find_all_ddj_by_year($request->request->get('annee'), $l_hotel);
                $pagination = $paginator->paginate(
                    $query, /* query NOT result */
                    $request->query->getInt('page', 1), /*page number*/
                    10 /*limit per page*/
                );
                //dd($pagination);
                $user = $data_session['user'];
                $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
                if ($pos == "impossible") {
                    return $this->render('/page/error.html.twig');
                } else {
                    return $this->render('page/crj.html.twig', [
                        "pagination" => $pagination,
                        "id" => "li__compte_rendu",
                        "hotel" => $data_session['pseudo_hotel'],
                        "current_page" => $data_session['current_page'],
                        "tab_annee" => $tab_sans_doublant,
                        "annee_courante" => $request->request->get('annee'),
                        "tropical_wood"     => false,
                    ]);
                }
            }
            else{
                $date1 = date_create($request->request->get('date1'));
                $date2 = date_create($request->request->get('date2'));
                $query = $repoDoneeDJ->find_all_ddj_between($date1, $date2, $l_hotel);
                //dd($j);
                $pagination = $paginator->paginate(
                    $query, /* query NOT result */
                    $request->query->getInt('page', 1), /*page number*/
                    10 /*limit per page*/
                );
                //dd($pagination);
                $user = $data_session['user'];
                $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
                if ($pos == "impossible") {
                    return $this->render('/page/error.html.twig');
                } else {
                    return $this->render('page/crj.html.twig', [
                        "pagination"            => $pagination,
                        "id"                    => "li__compte_rendu",
                        "date1"                 => $date1->format('Y-m-d'),
                        "date2"                 => $date2->format('Y-m-d'),
                        "hotel"                 => $data_session['pseudo_hotel'],
                        "current_page"          => $data_session['current_page'],
                        "tab_annee"             => $tab_sans_doublant,
                        "annee_courante"        => $request->request->get('annee'),
                        'tropical_wood'         => false,
                    ]);
                }
            }
        }
        else{
            /** Pour la pagination */

            $query = $repoDoneeDJ->find_all_ddj($l_hotel);
            //dd($query);
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            /** fin pour la pagination */
            /** fin requete */

            $user = $data_session['user'];
            $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
            if ($pos == "impossible") {
                return $this->render('/page/error.html.twig');
            } else {
                
                return $this->render('page/crj.html.twig', [
                    "pagination"            => $pagination,
                    "id"                    => "li__compte_rendu",
                    "hotel"                 => $data_session['pseudo_hotel'],
                    "current_page"          => $data_session['current_page'],
                    "tab_annee"             => $tab_sans_doublant,
                    'tropical_wood'         => false,
                ]);
            }
        }
       
    }

    /**
     * @Route("/profile/{pseudo_hotel}/hebergement", name="hebergement")
    */
    public function hebergement(Services $services, $pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        
        $response = new Response();
        $data_session = $session->get('hotel');
        
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "hebergement";
        
        if($pseudo_hotel == 'tous'){
            $pseudo_hotel = 'royal_beach';
        }
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        
        if ($request->isXmlHttpRequest()) {

            $nom_client = $request->get('nom_client');
            $prenom_client = (empty($request->get('prenom_client'))) ? "" : $request->get('prenom_client');
            $date_arrivee = $request->get('date_arrivee');
            $date_depart = $request->get('date_depart');
            $createdAt = date_create($request->get('createdAt'));
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
                $client->setCreatedAt($createdAt);
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
        $all_ddj = $repoDoneeDJ->findAll();
    
        //dd($all_ddj);
        $current_hotel_ddj = [];
        //dd($current_hotel_ddj);
        $tab_annee = [];
        $tab_sans_doublant = [];
        if(count($all_ddj) > 0){
            foreach ($all_ddj as $d) {
                $son_hotel = $d->getHotel()->getPseudo();
                //dd($son_hotel);
                if ($son_hotel == $pseudo_hotel) {
                    array_push($current_hotel_ddj, $d);
                }
            }
            
            foreach ($current_hotel_ddj as $c) {
                $son_created_at = $c->getCreatedAt();
                $annee = $son_created_at->format('Y');
                array_push($tab_annee, $annee);
            }

            if (count($tab_annee) > 0) {
                array_push($tab_sans_doublant, $tab_annee[0]);
            }
            for ($i = 0; $i < count($tab_annee); $i++) {

                if (!in_array($tab_annee[$i], $tab_sans_doublant)) {
                    array_push($tab_sans_doublant, $tab_annee[$i]);
                }
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

        $heb_pax_jan = 0;
        $heb_pax_fev = 0;
        $heb_pax_mars = 0;
        $heb_pax_avr = 0;
        $heb_pax_mai = 0;
        $heb_pax_juin = 0;
        $heb_pax_juil = 0;
        $heb_pax_aou = 0;
        $heb_pax_sep = 0;
        $heb_pax_oct = 0;
        $heb_pax_nov = 0;
        $heb_pax_dec = 0;

        $heb_occ_jan = 0;
        $heb_occ_fev = 0;
        $heb_occ_mars = 0;
        $heb_occ_avr = 0;
        $heb_occ_mai = 0;
        $heb_occ_juin = 0;
        $heb_occ_juil = 0;
        $heb_occ_aou = 0;
        $heb_occ_sep = 0;
        $heb_occ_oct = 0;
        $heb_occ_nov = 0;
        $heb_occ_dec = 0;

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

        $pax_jan = 0;
        $pax_fev = 0;
        $pax_mars = 0;
        $pax_avr = 0;
        $pax_mai = 0;
        $pax_juin = 0;
        $pax_juil = 0;
        $pax_aou = 0;
        $pax_sep = 0;
        $pax_oct = 0;
        $pax_nov = 0;
        $pax_dec = 0;

        $occ_jan = 0;
        $occ_fev = 0;
        $occ_mars = 0;
        $occ_avr = 0;
        $occ_mai = 0;
        $occ_juin = 0;
        $occ_juil = 0;
        $occ_aou = 0;
        $occ_sep = 0;
        $occ_oct = 0;
        $occ_nov = 0;
        $occ_dec = 0;

        
        
        $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
        
        $annee_actuel = new \DateTime() ;
        $annee_actuel = $annee_actuel->format("Y");
        

        foreach ($all_ddj as $ddj) {
            $son_createdAt = $ddj->getCreatedAt();
            $son_mois_ca = $son_createdAt->format("m");
            $son_annee_ca = $son_createdAt->format("Y");
            if ($son_annee_ca == $annee_actuel) {
                if ($son_mois_ca == "01") {
                    $eca_jan++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_jan += $x;

                    $e_jan++;
                    $heb_to_jan += $ddj->getHebTo();

                    $pax_jan++;
                    $heb_pax_jan += intval($ddj->getNPaxHeb());

                    $occ_jan++;
                    $heb_occ_jan += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "02") {
                    $eca_fev++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_fev += $x;

                    $e_fev++;
                    $heb_to_fev += $ddj->getHebTo();

                    $pax_fev++;
                    $heb_pax_fev += intval($ddj->getNPaxHeb());

                    $occ_fev++;
                    $heb_occ_fev += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "03") {
                    $eca_mars++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_mars += $x;

                    $e_mars++;
                    $heb_to_mars += $ddj->getHebTo();

                    $pax_mars++;
                    $heb_pax_mars += intval($ddj->getNPaxHeb());

                    $occ_mars++;
                    $heb_occ_mars += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "04") {
                    $eca_avr++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_avr += $x;

                    $e_avr++;
                    $heb_to_avr += $ddj->getHebTo();

                    $pax_avr++;
                    $heb_pax_avr += intval($ddj->getNPaxHeb());

                    $occ_avr++;
                    $heb_occ_avr += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "05") {
                    $eca_mai++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_mai += $x;

                    $e_mai++;
                    $heb_to_mai += $ddj->getHebTo();

                    $pax_mai++;
                    $heb_pax_mai += intval($ddj->getNPaxHeb());

                    $occ_mai++;
                    $heb_occ_mai += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "06") {
                    $eca_juin++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_juin += $x;

                    $e_juin++;
                    $heb_to_juin += $ddj->getHebTo();

                    $pax_juin++;
                    $heb_pax_juin += intval($ddj->getNPaxHeb());

                    $occ_juin++;
                    $heb_occ_juin += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "07") {
                    $eca_juil++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_juil += $x;

                    $e_juil++;
                    $heb_to_juil += $ddj->getHebTo();

                    $pax_juil++;
                    $heb_pax_juil += intval($ddj->getNPaxHeb());

                    $occ_juil++;
                    $heb_occ_juil += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "08") {
                    $eca_aou++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_aou += $x;

                    $e_aou++;
                    $heb_to_aou += $ddj->getHebTo();

                    $pax_aou++;
                    $heb_pax_aou += intval($ddj->getNPaxHeb());

                    $occ_aou++;
                    $heb_occ_aou += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "09") {
                    $eca_sep++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_sep += $x;

                    $e_sep++;
                    $heb_to_sep += $ddj->getHebTo();

                    $pax_sep++;
                    $heb_pax_sep += intval($ddj->getNPaxHeb());

                    $occ_sep++;
                    $heb_occ_sep += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "10") {
                    $eca_oct++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                   
                    $heb_ca_oct += $x;

                    $e_oct++;
                    $heb_to_oct += $ddj->getHebTo();

                    $pax_oct++;
                    $heb_pax_oct += intval($ddj->getNPaxHeb());

                    $occ_oct++;
                    $heb_occ_oct += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "11") {
                    
                    $eca_nov++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_nov += $x;

                    $e_nov++;
                    $heb_to_nov += $ddj->getHebTo();

                    $pax_nov++;
                    $heb_pax_nov += intval($ddj->getNPaxHeb());

                    $occ_nov++;
                    $heb_occ_nov += intval($ddj->getNChambreOccupe());
                }
                if ($son_mois_ca == "12") {
                    
                    $eca_dec++;
                    $x = floatval(str_replace(' ', '', $ddj->getHebCa()));
                    $heb_ca_dec += $x;

                    $e_dec++;
                    $heb_to_dec += $ddj->getHebTo();

                    $pax_dec++;
                    $heb_pax_dec += intval($ddj->getNPaxHeb());

                    $occ_dec++;
                    $heb_occ_dec += intval($ddj->getNChambreOccupe());
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
        for ($i = 0; $i < count($tab_eca); $i++) {
            if ($tab_eca[$i] == 0) {
                $tab_eca[$i] = 1;
            }
           
            $tab_heb_ca[$i] = floatval(str_replace(' ', '', $tab_heb_ca[$i])) ;
            
        }

        $tab_heb_pax = [$heb_pax_jan, $heb_pax_fev, $heb_pax_mars, $heb_pax_avr, $heb_pax_mai, $heb_pax_juin, $heb_pax_juil, $heb_pax_aou, $heb_pax_sep, $heb_pax_oct, $heb_pax_nov, $heb_pax_dec];

        $tab_heb_occ = [$heb_occ_jan, $heb_occ_fev, $heb_occ_mars, $heb_occ_avr, $heb_occ_mai, $heb_occ_juin, $heb_occ_juil, $heb_occ_aou, $heb_occ_sep, $heb_occ_oct, $heb_occ_nov, $heb_occ_dec];
        
        $tab_labels = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec"];
        $tab_aff = [];

        // affichage des KPI

        $allAnnee = $this->tab_annee($pseudo_hotel);
        $taille_allAnnee = count($allAnnee);

        $today = new \DateTime();
        $annee = $today->format("Y");
        if ($taille_allAnnee > 0) {
            $annee = $allAnnee[$taille_allAnnee - 1];
        }
        $tab_adr = [];
        $tab_revp = [];
        
       
        $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $all_dm = $repoDM->findBy(['hotel' => $hotel]);
        //dd($all_dm);
        $year_mens = [];
        if (count($all_dm) > 0) {
            foreach ($all_dm as $item) {
                $adr = str_replace(' ','', $item->getKpiAdr());
                $revp = str_replace(' ', '', $item->getKpiRevP());
                $son_mois = $item->getMois();
                $tab_explode = explode("-", $son_mois);
                $son_annee = $tab_explode[1];
                if(!in_array($son_annee, $year_mens)){
                    array_push($year_mens ,$son_annee);
                }
                if ($son_annee == $annee) {
                    $son_numero_mois = intVal($tab_explode[0]) - 1;
                    $tab_adr[$son_numero_mois] = $adr;
                    $tab_revp[$son_numero_mois] = $revp;
                    
                }
            }
        }
        
        sort($year_mens);
        ksort($tab_adr);
        ksort($tab_revp);
        
        if ($request->request->count() > 0) {
                
            if($request->request->get('action')){
               if($request->request->get('action') == "modification"){
                    //dd($request->request);
                    $client_id = $request->request->get('client_id');
                    $nom = $request->request->get('nom');
                    $prenom = $request->request->get('prenom');
                    $date_arrivee_client = $request->request->get('date_arrivee');
                    $date_depart_client = $request->request->get('date_depart');

                    $date_arrivee_client = date_create($date_arrivee_client);
                    $date_depart_client = date_create($date_depart_client);
                    $diff = $date_arrivee_client->diff($date_depart_client);
                    $days = $diff->d;

                    $client = $repo->find($client_id);
                    $client->setNom($nom);
                    $client->setPrenom($prenom);
                    $client->setDateArrivee($date_arrivee_client);
                    $client->setDateDepart($date_depart_client);
                    $client->setDureeSejour($days);
                    $manager->persist($client);
                    $manager->flush();
               }
                if ($request->request->get('action') == "suppression") {
                    //dd($request->request);
                    $client_id = $request->request->get('client_id');
                   

                    $client = $repo->find($client_id);
                  
                    $manager->remove($client);
                    $manager->flush();
                }
            }
            //dd($request->request);
            $date1 = $request->request->get('date1');
            $date2 = $request->request->get('date2');
            
            $datte_text_1 = $services->toMonthText($date1);
            $datte_text_2 = $services->toMonthText($date2);

            $date1 = date_create($date1);
            $date2 = date_create($date2);

            $datte_text_1 = $services->toMonthText($date1->format('d-m-Y'));
            $datte_text_2 = $services->toMonthText($date2->format('d-m-Y'));

            $all_date_asked = $services->all_date_between2_dates($date1, $date2);
            //dd($all_date_asked);
            
            $tab = [];
            $data_session = $session->get('hotel');
            $data_session['current_page'] = "crj";
            $data_session['pseudo_hotel'] = $pseudo_hotel;
            $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $current_id_hotel = $l_hotel->getId();
            $clients = $repo->findAll();
            foreach ($clients as $item) {
                $son_id_hotel = $item->getHotel()->getId();
                if ($son_id_hotel == $current_id_hotel) {
                    array_push($tab, $item);
                }
            }
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
            
            return $this->render('page/hebergement.html.twig', [
                "id"                    => "li__hebergement",
                "tab_annee"             => $tab_sans_doublant,
                "hotel"                 => $data_session['pseudo_hotel'],
                "current_page"          => $data_session['current_page'],
                "tab_heb_to"            => $tab_heb_to,
                "tab_heb_ca"            => $tab_heb_ca,
                "tab_labels"            => $tab_labels,
                "type_affichage"        => "annee",
                'items'                 => $tab_aff,
                'date1'                 => $date1->format('Y-m-d'),
                'date2'                 => $date2->format('Y-m-d'),
                "tropical_wood"         => false,
                "interval_text_date"    => $datte_text_1 . " et " . $datte_text_2,
                "tab_adr"               => $tab_adr,
                "tab_revp"              => $tab_revp,
                "annee"                 => $annee,
                "year_mens"             => $year_mens

            ]);
           
        }

        

        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
       else if ($pos != "impossible"){
            
            $clients = $repo->findBy(
                ['hotel' => $hotel],
                ['createdAt' => "DESC"]
            );
            $tab_aff = [];
            $data_repere = new \DateTime();
            $date_repere_obj = date_create($data_repere->format('d-m-Y'));
            $date_text_aff = $data_repere->format("d-m-Y");
            $date_text_aff = $services->toMonthText($date_text_aff);
           
            foreach($clients as $client){
                $sa_date_arr = $client->getDateArrivee();
                $sa_date_depart = $client->getDateDepart();
                if($sa_date_arr <= $date_repere_obj && $sa_date_depart >= $date_repere_obj){
                    array_push($tab_aff, $client);
                }
            }
           
            return $this->render('page/hebergement.html.twig', [
                "id"                    => "li__hebergement",
                "tab_annee"             => $tab_sans_doublant,
                "hotel"                 => $data_session['pseudo_hotel'],
                "current_page"          => $data_session['current_page'],
                "tab_heb_to"            => $tab_heb_to,
                "tab_heb_ca"            => $tab_heb_ca,
                "tab_heb_pax"           => $tab_heb_pax,
                "tab_heb_occ"           => $tab_heb_occ,
                "tab_labels"            => $tab_labels,
                "type_affichage"        => "annee",
                'items'                 => $tab_aff,
                "tropical_wood"         => false,
                "date_text_month"       => $date_text_aff,
                "tab_adr"               => $tab_adr,
                "tab_revp"              => $tab_revp,
                "annee"                 => $annee,
                "year_mens"             => $year_mens
            ]);
       }
    }

    public function tab_annee($pseudo_hotel): array
    {

        $repoDm = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
        $allDm = $repoDm->findAll();
        $allAnnee = [];
        foreach ($allDm as $item) {
            $son_pseudo_hotel = $item->getHotel()->getPseudo();
            if ($pseudo_hotel == $son_pseudo_hotel) {
                $t = explode("-", $item->getMois());
                $son_annee = $t[1];
                if (!in_array($son_annee, $allAnnee)) {
                    array_push($allAnnee, $son_annee);
                }
            }
        }
        sort($allAnnee);
        return $allAnnee;
    }
    


    /**
     * @Route("/profile/{pseudo_hotel}/restaurant", name="restaurant")
     */
    public function restaurant(SessionInterface $session, $pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "restaurant";
        $data_session['pseudo_hotel'] = $pseudo_hotel;

        /** préparation des input des filtres */
        // les année existant dans les donnée de jour pour l'hotel en cours
        $all_ddj = $repoDoneeDJ->findAll();
        $current_hotel_ddj = [];
        foreach ($all_ddj as $d) {
            $son_hotel = $d->getHotel()->getPseudo();
            if ($son_hotel == $pseudo_hotel) {
                array_push($current_hotel_ddj, $d);
            }
        }
        //dd($current_hotel_ddj);
        $tab_annee = [];
        $tab_sans_doublant = [];
        foreach ($current_hotel_ddj as $c) {
            $son_created_at = $c->getCreatedAt();
            $annee = $son_created_at->format('Y');
            array_push($tab_annee, $annee);
        }
        if (count($tab_annee) > 0) {
            array_push($tab_sans_doublant, $tab_annee[0]);
        }
        for ($i = 0; $i < count($tab_annee); $i++) {

            if (!in_array($tab_annee[$i], $tab_sans_doublant)) {
                array_push($tab_sans_doublant, $tab_annee[$i]);
            }
        }
        //dd($tab_sans_doublant);

        // les res_ca pour chaque mois

        $res_ca_jan = 0;
        $res_ca_fev = 0;
        $res_ca_mars = 0;
        $res_ca_avr = 0;
        $res_ca_mai = 0;
        $res_ca_juin = 0;
        $res_ca_juil = 0;
        $res_ca_aou = 0;
        $res_ca_sep = 0;
        $res_ca_oct = 0;
        $res_ca_nov = 0;
        $res_ca_dec = 0;

        // les res_pd pour chaque mois

        $res_pd_jan = 0;
        $res_pd_fev = 0;
        $res_pd_mars = 0;
        $res_pd_avr = 0;
        $res_pd_mai = 0;
        $res_pd_juin = 0;
        $res_pd_juil = 0;
        $res_pd_aou = 0;
        $res_pd_sep = 0;
        $res_pd_oct = 0;
        $res_pd_nov = 0;
        $res_pd_dec = 0;

        // les res_d pour chaque mois

        $res_d_jan = 0;
        $res_d_fev = 0;
        $res_d_mars = 0;
        $res_d_avr = 0;
        $res_d_mai = 0;
        $res_d_juin = 0;
        $res_d_juil = 0;
        $res_d_aou = 0;
        $res_d_sep = 0;
        $res_d_oct = 0;
        $res_d_nov = 0;
        $res_d_dec = 0;

        // les res_d pour chaque mois

        $res_di_jan = 0;
        $res_di_fev = 0;
        $res_di_mars = 0;
        $res_di_avr = 0;
        $res_di_mai = 0;
        $res_di_juin = 0;
        $res_di_juil = 0;
        $res_di_aou = 0;
        $res_di_sep = 0;
        $res_di_oct = 0;
        $res_di_nov = 0;
        $res_di_dec = 0;

        // effectif pour la moyen rec_ca

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

        // effectif pour la moyen rec_pd

        $epd_jan = 0;
        $epd_fev = 0;
        $epd_mars = 0;
        $epd_avr = 0;
        $epd_mai = 0;
        $epd_juin = 0;
        $epd_juil = 0;
        $epd_aou = 0;
        $epd_sep = 0;
        $epd_oct = 0;
        $epd_nov = 0;
        $epd_dec = 0;

        // effectif pour la moyen res_d

        $ed_jan = 0;
        $ed_fev = 0;
        $ed_mars = 0;
        $ed_avr = 0;
        $ed_mai = 0;
        $ed_juin = 0;
        $ed_juil = 0;
        $ed_aou = 0;
        $ed_sep = 0;
        $ed_oct = 0;
        $ed_nov = 0;
        $ed_dec = 0;

        // effectif pour la moyen res_di

        $edi_jan = 0;
        $edi_fev = 0;
        $edi_mars = 0;
        $edi_avr = 0;
        $edi_mai = 0;
        $edi_juin = 0;
        $edi_juil = 0;
        $edi_aou = 0;
        $edi_sep = 0;
        $edi_oct = 0;
        $edi_nov = 0;
        $edi_dec = 0;
        
        $today = new \DateTime();
        $annee_actuel = $today->format('Y');
        
        foreach ($current_hotel_ddj as $ddj) {
            $son_createdAt = $ddj->getCreatedAt();
            $son_mois_ca = $son_createdAt->format("m");
            $son_annee_ca = $son_createdAt->format("Y");
            if ($son_annee_ca == $annee_actuel) {
                if ($son_mois_ca == "01") {
                    $epd_jan++;
                    $res_pd_jan += intval($ddj->getResPDej());

                    $ed_jan++;
                    $res_d_jan += intval($ddj->getResDej());

                    $edi_jan++;
                    $res_di_jan += intval($ddj->getResDinner());

                    $eca_jan++;
                    $res_ca_jan += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "02") {
                    $epd_fev++;
                    $res_pd_fev += intval($ddj->getResPDej());

                    $ed_fev++;
                    $res_d_fev += intval($ddj->getResDej());

                    $edi_fev++;
                    $res_di_fev += intval($ddj->getResDinner());

                    $eca_fev++;
                    $res_ca_fev += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "03") {
                    $epd_mars++;
                    $res_pd_mars += intval($ddj->getResPDej());

                    $ed_mars++;
                    $res_d_mars += intval($ddj->getResDej());

                    $edi_mars++;
                    $res_di_mars += intval($ddj->getResDinner());

                    $eca_mars++;
                    $res_ca_mars += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "04") {
                    $epd_avr++;
                    $res_pd_avr += intval($ddj->getResPDej());

                    $ed_avr++;
                    $res_d_avr += intval($ddj->getResDej());

                    $edi_avr++;
                    $res_di_avr += intval($ddj->getResDinner());

                    $eca_avr++;
                    $res_ca_avr += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "05") {
                    $epd_mai++;
                    $res_pd_mai += intval($ddj->getResPDej());

                    $ed_mai++;
                    $res_d_mai += intval($ddj->getResDej());

                    $edi_mai++;
                    $res_di_mai += intval($ddj->getResDinner());

                    $eca_mai++;
                    $res_ca_mai += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "06") {
                    $epd_juin++;
                    $res_pd_juin += intval($ddj->getResPDej());

                    $ed_juin++;
                    $res_d_juin += intval($ddj->getResDej());

                    $edi_juin++;
                    $res_di_juin += intval($ddj->getResDinner());

                    $eca_juin++;
                    $res_ca_juin += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "07") {
                    $epd_juil++;
                    $res_pd_juil += intval($ddj->getResPDej());

                    $ed_juil++;
                    $res_d_juil += intval($ddj->getResDej());

                    $edi_juil++;
                    $res_di_juil += intval($ddj->getResDinner());

                    $eca_juil++;
                    $res_ca_juil += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "08") {
                    $epd_aou++;
                    $res_pd_aou += intval($ddj->getResPDej());

                    $ed_aou++;
                    $res_d_aou += intval($ddj->getResDej());

                    $edi_aou++;
                    $res_di_aou += intval($ddj->getResDinner());

                    $eca_aou++;
                    $res_ca_aou += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "09") {
                    $epd_sep++;
                    $res_pd_sep += intval($ddj->getResPDej());

                    $ed_sep++;
                    $res_d_sep += intval($ddj->getResDej());

                    $edi_sep++;
                    $res_di_sep += intval($ddj->getResDinner());

                    $eca_sep++;
                    $res_ca_sep += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "10") {
                    $epd_oct++;
                    $res_pd_oct += intval($ddj->getResPDej());

                    $ed_oct++;
                    $res_d_oct += intval($ddj->getResDej());

                    $edi_oct++;
                    $res_di_oct += intval($ddj->getResDinner());

                    $eca_oct++;
                    $res_ca_oct += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "11") {
                    $epd_nov++;
                    $res_pd_nov += intval($ddj->getResPDej());

                    $ed_nov++;
                    $res_d_nov += intval($ddj->getResDej());

                    $edi_nov++;
                    $res_di_nov += intval($ddj->getResDinner());

                    $eca_nov++;
                    $res_ca_nov += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
                if ($son_mois_ca == "12") {
                    $epd_dec++;
                    $res_pd_dec += intval($ddj->getResPDej());

                    $ed_dec++;
                    $res_d_dec += intval($ddj->getResDej());

                    $edi_dec++;
                    $res_di_dec += intval($ddj->getResDinner());

                    $eca_dec++;
                    $res_ca_dec += floatval(str_replace(' ', '', $ddj->getResCa()));
                }
            }
        }

        $tab_res_ca = [$res_ca_jan, $res_ca_fev, $res_ca_mars, $res_ca_avr, $res_ca_mai, $res_ca_juin, $res_ca_juil, $res_ca_aou, $res_ca_sep, $res_ca_oct, $res_ca_nov, $res_ca_dec];
        $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
        for ($i = 0; $i < count($tab_eca); $i++) {
            if ($tab_eca[$i] == 0) {
                $tab_eca[$i] = 1;
            }
            //$tab_res_ca[$i] = $tab_res_ca[$i] / $tab_eca[$i]; // / 10^6 car l'unité de graphe est le million
            // $tab_res_ca[$i] = floatval(str_replace(' ', '', $tab_res_ca[$i])) ;
            $tab_res_ca[$i] = floatval(str_replace(' ', '', $tab_res_ca[$i])) ;
            // $tab_res_ca[$i] = number_format($tab_res_ca[$i], 2);
        }

        $tab_res_pd = [$res_pd_jan, $res_pd_fev, $res_pd_mars, $res_pd_avr, $res_pd_mai, $res_pd_juin, $res_pd_juil, $res_pd_aou, $res_pd_sep, $res_pd_oct, $res_pd_nov, $res_pd_dec];
        $tab_epd = [$epd_jan, $epd_fev, $epd_mars, $epd_avr, $epd_mai, $epd_juin, $epd_juil, $epd_aou, $epd_sep, $epd_oct, $epd_nov, $epd_dec];
        for ($i = 0; $i < count($tab_epd); $i++) {
            if ($tab_epd[$i] == 0) {
                $tab_epd[$i] = 1;
            }
            $tab_res_pd[$i] = intval(($tab_res_pd[$i] / $tab_epd[$i]));
        }

        //dd($tab_res_d);

        $tab_res_d = [$res_d_jan, $res_d_fev, $res_d_mars, $res_d_avr, $res_d_mai, $res_d_juin, $res_d_juil, $res_d_aou, $res_d_sep, $res_d_oct, $res_d_nov, $res_d_dec];
        $tab_ed = [$ed_jan, $ed_fev, $ed_mars, $ed_avr, $ed_mai, $ed_juin, $ed_juil, $ed_aou, $ed_sep, $ed_oct, $ed_nov, $ed_dec];
        for ($i = 0; $i < count($tab_ed); $i++) {
            if ($tab_ed[$i] == 0) {
                $tab_ed[$i] = 1;
            }
            $tab_res_d[$i] = intval(($tab_res_d[$i] / $tab_ed[$i]));
        }

        //dd($tab_res_d);


        $tab_res_di = [$res_di_jan, $res_di_fev, $res_di_mars, $res_di_avr, $res_di_mai, $res_di_juin, $res_di_juil, $res_di_aou, $res_di_sep, $res_di_oct, $res_di_nov, $res_di_dec];
        $tab_edi = [$edi_jan, $edi_fev, $edi_mars, $edi_avr, $edi_mai, $edi_juin, $edi_juil, $edi_aou, $edi_sep, $edi_oct, $edi_nov, $edi_dec];
        for ($i = 0; $i < count($tab_edi); $i++) {
            if ($tab_edi[$i] == 0) {
                $tab_edi[$i] = 1;
            }
            $tab_res_di[$i] = intval(($tab_res_di[$i] / $tab_edi[$i]));
        }

        //dd($tab_res_di);

        // total 
        $tab_total = [];
        for ($i = 0; $i < 12; $i++) {
            $x = 0 ;
            $x += $tab_res_pd[$i];
            $x += $tab_res_d[$i];
            $x += $tab_res_di[$i];
            array_push($tab_total, $x);
        }

        //dd($tab_total);

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
        // HotelRepository $repoHotel
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
       else{
            return $this->render('page/restaurant.html.twig', [
                "id" => "li__restaurant",
                "hotel" => $data_session['pseudo_hotel'],
                "current_page" => $data_session['current_page'],
                "tab_annee" => $tab_sans_doublant,
                "tab_res_ca" => $tab_res_ca,
                "tab_res_pd" => $tab_res_pd,
                "tab_res_d" => $tab_res_d,
                "tab_res_di" => $tab_res_di,
                'tab_res_total' => $tab_total,
                "tropical_wood"     => false,
            ]);
       }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/spa", name="spa")
     */
    public function spa(SessionInterface $session, $pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "spa";
        $data_session['pseudo_hotel'] = $pseudo_hotel;

        /** préparation des input des filtres */
        // les année existant dans les donnée de jour pour l'hotel en cours
        $all_ddj = $repoDoneeDJ->findAll();
        $current_hotel_ddj = [];
        foreach ($all_ddj as $d) {
            $son_hotel = $d->getHotel()->getPseudo();
            if ($son_hotel == $pseudo_hotel) {
                array_push($current_hotel_ddj, $d);
            }
        }
        //dd($current_hotel_ddj);
        $tab_annee = [];
        $tab_sans_doublant = [];
        foreach ($current_hotel_ddj as $c) {
            $son_created_at = $c->getCreatedAt();
            $annee = $son_created_at->format('Y');
            array_push($tab_annee, $annee);
        }
        if(count($tab_annee) > 0){
            array_push($tab_sans_doublant, $tab_annee[0]);
        }
        
        for ($i = 0; $i < count($tab_annee); $i++) {

            if (!in_array($tab_annee[$i], $tab_sans_doublant)) {
                array_push($tab_sans_doublant, $tab_annee[$i]);
            }
        }
        //dd($tab_sans_doublant);

        // les spa_ca_jan pour chaque mois

        $spa_ca_jan = 0;
        $spa_ca_fev = 0;
        $spa_ca_mars = 0;
        $spa_ca_avr = 0;
        $spa_ca_mai = 0;
        $spa_ca_juin = 0;
        $spa_ca_juil = 0;
        $spa_ca_aou = 0;
        $spa_ca_sep = 0;
        $spa_ca_oct = 0;
        $spa_ca_nov = 0;
        $spa_ca_dec = 0;

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

        // les spa_na_jan pour chaque mois

        $spa_na_jan = 0;
        $spa_na_fev = 0;
        $spa_na_mars = 0;
        $spa_na_avr = 0;
        $spa_na_mai = 0;
        $spa_na_juin = 0;
        $spa_na_juil = 0;
        $spa_na_aou = 0;
        $spa_na_sep = 0;
        $spa_na_oct = 0;
        $spa_na_nov = 0;
        $spa_na_dec = 0;

        // effectif pour la moyen 

        $ena_jan = 0;
        $ena_fev = 0;
        $ena_mars = 0;
        $ena_avr = 0;
        $ena_mai = 0;
        $ena_juin = 0;
        $ena_juil = 0;
        $ena_aou = 0;
        $ena_sep = 0;
        $ena_oct = 0;
        $ena_nov = 0;
        $ena_dec = 0;

        // les spa_cu_jan pour chaque mois

        $spa_cu_jan = 0;
        $spa_cu_fev = 0;
        $spa_cu_mars = 0;
        $spa_cu_avr = 0;
        $spa_cu_mai = 0;
        $spa_cu_juin = 0;
        $spa_cu_juil = 0;
        $spa_cu_aou = 0;
        $spa_cu_sep = 0;
        $spa_cu_oct = 0;
        $spa_cu_nov = 0;
        $spa_cu_dec = 0;

        // effectif pour la moyen 

        $ecu_jan = 0;
        $ecu_fev = 0;
        $ecu_mars = 0;
        $ecu_avr = 0;
        $ecu_mai = 0;
        $ecu_juin = 0;
        $ecu_juil = 0;
        $ecu_aou = 0;
        $ecu_sep = 0;
        $ecu_oct = 0;
        $ecu_nov = 0;
        $ecu_dec = 0;

        $all_ddj = $repoDoneeDJ->findAll();
        $annee_actuel = new \DateTime();
        $annee_actuel = $annee_actuel->format("Y");
        //dd($annee_actuel);
        foreach ($current_hotel_ddj as $ddj) {
            $son_createdAt = $ddj->getCreatedAt();
            $son_mois_ca = $son_createdAt->format("m");
            $son_annee_ca = $son_createdAt->format("Y");
            if ($son_annee_ca == $annee_actuel) {
                if ($son_mois_ca == "01") {
                    $eca_jan++;
                    $spa_ca_jan += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_jan++;
                    $spa_na_jan += $ddj->getSpaNAbonne();

                    $ecu_jan++;
                    $spa_cu_jan += $ddj->getSpaCUnique();
                }
                if ($son_mois_ca == "02") {
                    $eca_fev++;
                    $spa_ca_fev += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_fev++;
                    $spa_na_fev += $ddj->getSpaNAbonne();

                    $ecu_fev++;
                    $spa_cu_fev += $ddj->getSpaCUnique();
                }
                if ($son_mois_ca == "03") {
                    $eca_mars++;
                    $spa_ca_mars += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_mars++;
                    $spa_na_mars += $ddj->getSpaNAbonne();

                    $ecu_mars++;
                    $spa_cu_mars += $ddj->getSpaCUnique();
                }
                if ($son_mois_ca == "04") {
                    $eca_avr++;
                    $spa_ca_avr += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_avr++;
                    $spa_na_avr += $ddj->getSpaNAbonne();

                    $ecu_avr++;
                    $spa_cu_avr += $ddj->getSpaCUnique();
                    
                }
                if ($son_mois_ca == "05") {
                    
                    $eca_mai++;
                    $spa_ca_mai += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_mai++;
                    $spa_na_mai += $ddj->getSpaNAbonne();

                    $ecu_mai++;
                    $spa_cu_mai += $ddj->getSpaCUnique();
                    
                }
                if ($son_mois_ca == "06") {
                    $eca_juin++;
                    $spa_ca_juin += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_juin++;
                    $spa_na_juin += $ddj->getSpaNAbonne();

                    $ecu_juin++;
                    $spa_cu_juin += $ddj->getSpaCUnique();

                }
                if ($son_mois_ca == "07") {
                    $eca_juil++;
                    $spa_ca_juil += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_juil++;
                    $spa_na_juil += $ddj->getSpaNAbonne();

                    $ecu_juil++;
                    $spa_cu_juil += $ddj->getSpaCUnique();

                }
                if ($son_mois_ca == "08") {
                    $eca_aou++;
                    $spa_ca_aou += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_aou++;
                    $spa_na_aou += $ddj->getSpaNAbonne();

                    $ecu_aou++;
                    $spa_cu_aou += $ddj->getSpaCUnique();

                }
                if ($son_mois_ca == "09") {
                    $eca_sep++;
                    $spa_ca_sep += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_sep++;
                    $spa_na_sep += $ddj->getSpaNAbonne();

                    $ecu_sep++;
                    $spa_cu_sep += $ddj->getSpaCUnique();

                }
                if ($son_mois_ca == "10") {
                    $eca_oct++;
                    $spa_ca_oct += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_oct++;
                    $spa_na_oct += $ddj->getSpaNAbonne();

                    $ecu_oct++;
                    $spa_cu_oct += $ddj->getSpaCUnique();
                }
                if ($son_mois_ca == "11") {
                    $eca_nov++;
                    $spa_ca_nov += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_nov++;
                    $spa_na_nov += $ddj->getSpaNAbonne();

                    $ecu_nov++;
                    $spa_cu_nov += $ddj->getSpaCUnique();
                }
                if ($son_mois_ca == "12") {
                    $eca_dec++;
                    $spa_ca_dec += floatval(str_replace(' ', '', $ddj->getSpaCa()));

                    $ena_dec++;
                    $spa_na_dec += $ddj->getSpaNAbonne();

                    $ecu_dec++;
                    $spa_cu_dec += $ddj->getSpaCUnique();
                    
                }
            }
        }


        $tab_spa_ca = [$spa_ca_jan, $spa_ca_fev, $spa_ca_mars, $spa_ca_avr, $spa_ca_mai, $spa_ca_juin, $spa_ca_juil, $spa_ca_aou, $spa_ca_sep, $spa_ca_oct, $spa_ca_nov, $spa_ca_dec];
        $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
        for ($i = 0; $i < count($tab_eca); $i++) {
            if ($tab_eca[$i] == 0) {
                $tab_eca[$i] = 1;
            }
            //$tab_spa_ca[$i] = $tab_spa_ca[$i] / $tab_eca[$i]; // / 10^6 car l'unité de graphe est le million
            $tab_spa_ca[$i] = floatval(str_replace(' ', '', $tab_spa_ca[$i])) ;
            // $tab_spa_ca[$i] = number_format($tab_spa_ca[$i], 2);
        }

        $tab_spa_na = [$spa_na_jan, $spa_na_fev, $spa_na_mars, $spa_na_avr, $spa_na_mai, $spa_na_juin, $spa_na_juil, $spa_na_aou, $spa_na_sep, $spa_na_oct, $spa_na_nov, $spa_na_dec];
        $tab_ena = [$ena_jan, $ena_fev, $ena_mars, $ena_avr, $ena_mai, $ena_juin, $ena_juil, $ena_aou, $ena_sep, $ena_oct, $ena_nov, $ena_dec];
        for ($i = 0; $i < count($tab_ena); $i++) {
            if ($tab_ena[$i] == 0) {
                $tab_ena[$i] = 1;
            }
            //$tab_spa_na[$i] = number_format(($tab_spa_na[$i] / $tab_ena[$i]), 2);
            $tab_spa_na[$i] = intval($tab_spa_na[$i]);
        }

        $tab_spa_cu = [$spa_cu_jan, $spa_cu_fev, $spa_cu_mars, $spa_cu_avr, $spa_cu_mai, $spa_cu_juin, $spa_cu_juil, $spa_cu_aou, $spa_cu_sep, $spa_cu_oct, $spa_cu_nov, $spa_cu_dec];
        $tab_ecu = [$ecu_jan, $ecu_fev, $ecu_mars, $ecu_avr, $ecu_mai, $ecu_juin, $ecu_juil, $ecu_aou, $ecu_sep, $ecu_oct, $ecu_nov, $ecu_dec];
        for ($i = 0; $i < count($tab_ecu); $i++) {
            if ($tab_ecu[$i] == 0) {
                $tab_ecu[$i] = 1;
            }
            // $tab_spa_cu[$i] = number_format(($tab_spa_cu[$i] / $tab_ecu[$i]), 2);
            $tab_spa_cu[$i] = intval($tab_spa_cu[$i]);
        }

        //dd($tab_spa_cu);
        // HotelRepository $repoHotel
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
        else{
            return $this->render('page/spa.html.twig', [
                "id" => "li__spa",
                "hotel" => $data_session['pseudo_hotel'],
                "current_page" => $data_session['current_page'],
                "tab_annee" => $tab_sans_doublant,
                'tab_spa_cu' => $tab_spa_cu,
                'tab_spa_ca' => $tab_spa_ca,
                'tab_spa_na' => $tab_spa_na,
                "tropical_wood"     => false,
            ]);
        }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/fournisseur", name="fournisseur")
     */
    public function fournisseur(FournisseurRepository $repoFour, EntityManagerInterface $manager, Services $services, Request $request, SessionInterface $session, $pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "fournisseur";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $form_add = $this->createform(FournisseurFileType::class);
        $text = "tsisy";
        $form_add->handleRequest($request);

        if($form_add->isSubmitted() && $form_add->isValid()){
            $fichier = $form_add->get('fichier')->getData();
            //dd($fichier->getRealPath()); // tmp name
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            //dd($fichier->getClientOriginalName());
            //dd($originalFilename1);
            // this is needed to safely include the file name as part of the URL
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();
            //dd($fichier->guessExtension());
            //$allow_ext = ['xls', 'csv', 'xlsx'];

            $text .= " 1. Fichié bien arrivé <br>";
            // on supprime tous les données présent
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            $text .= " 2. Type de fichier reconnu <br>";
            //dd($fileType);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            $sheetname = "FOURNISSEURS";
            $reader->setLoadSheetsOnly($sheetname);
            $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
            //dd($spreadsheet);
            //$data = $spreadsheet->getActiveSheet()->toArray();
            //dd($spreadsheet);
            if($spreadsheet->getSheetByName("FOURNISSEURS")){
                $text .= " 3. Présence de feuille FOURNISSEURS <br>";
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $fours = $current_hotel->getFournisseurs();
                foreach ($fours as $item) {
                    $manager->remove($item);
                    $manager->flush();
                }
                $data = $spreadsheet->getSheetByName("FOURNISSEURS")->toArray();
                $text .= "on peut lire une donnée " . $data[5][0]." <br>";
                $error_fichier = 0;
                for ($i = 2; $i < count($data); $i++) {
                    $fournisseur = new Fournisseur();

                    $createdAtData = $data[$i][0];

                    $echeanceData = $data[$i][5];

                    $date_pmtData = $data[$i][8];

                    $numero_facture = $data[$i][1];
                    
                    $type = "";
                    if ($data[$i][2] != null) {
                        $type = $data[$i][2];
                    }

                    $nom_fournisseur = $data[$i][3];
                    $montant = 0;
                    if ($data[$i][4] != null) {
                        $montant = trim(intval($data[$i][4]));
                    }

                    $mode_pmt = "";
                    if ($data[$i][6] != null) {
                        $mode_pmt = $data[$i][6];
                    }

                    $montant_paye = 0;
                    $reste = $montant;
                    if ($data[$i][7] != null) {
                        $montant_paye =trim(intval($data[$i][7]));
                    }

                    $remarque = "";
                    if ($data[$i][9] != null) {
                        $remarque = $data[$i][9];
                    }

                    $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);

                    if ($numero_facture != "") {
                        $fournisseur->setNumeroFacture($numero_facture);
                        $fournisseur->setType($type);
                        $fournisseur->setNomFournisseur($nom_fournisseur);
                        $fournisseur->setMontant($montant);
                        $fournisseur->setReste($reste);
                        if ($montant_paye != 0) {
                            $reste = $montant - $montant_paye;
                            $fournisseur->setReste($reste);
                        }
                        $fournisseur->setModePmt($mode_pmt);
                        $fournisseur->setMontantPaye($montant_paye);

                        $fournisseur->setRemarque($remarque);
                        $fournisseur->addHotel($hotel);
                        if ($createdAtData != "") {
                            $createdAt_s = $services->parseMyDate($createdAtData);
                            $createdAt = date_create($createdAt_s);
                            if($createdAt){
                                $fournisseur->setCreatedAt($createdAt);
                            }
                            else{
                                $error_fichier = $i;
                                return $this->render('page/fournisseur.html.twig', [
                                    "test" => $text,
                                    "id"            => "li__fournisseur",
                                    "hotel"         => $data_session['pseudo_hotel'],
                                    "current_page"  => $data_session['current_page'],
                                    "form_add"      => $form_add->createView(),
                                    'date1' => $request->request->get('date1'),
                                    'date2' => $request->request->get('date2'),
                                    "tropical_wood"     => false,
                                    "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                                ]);
                            }
                        }

                        if ($echeanceData != "") {
                            $echeance_s = $services->parseMyDate($echeanceData);
                            $echeance = date_create($echeance_s);
                            if($echeance){
                                $fournisseur->setEcheance($echeance);
                            } else {
                                $error_fichier = $i;
                                return $this->render('page/fournisseur.html.twig', [
                                    "test" => $text,
                                    "id"            => "li__fournisseur",
                                    "hotel"         => $data_session['pseudo_hotel'],
                                    "current_page"  => $data_session['current_page'],
                                    "form_add"      => $form_add->createView(),
                                    'date1' => $request->request->get('date1'),
                                    'date2' => $request->request->get('date2'),
                                    "tropical_wood"     => false,
                                    "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                                ]);
                            }
                        }

                        if ($date_pmtData != "") {
                            $date_pmt_s = $services->parseMyDate($date_pmtData);
                            $date_pmt = date_create($date_pmt_s);
                            if($date_pmt){
                                $fournisseur->setDatePmt($date_pmt);
                            } else {
                                $error_fichier = $i;
                                return $this->render('page/fournisseur.html.twig', [
                                    "test" => $text,
                                    "id"            => "li__fournisseur",
                                    "hotel"         => $data_session['pseudo_hotel'],
                                    "current_page"  => $data_session['current_page'],
                                    "form_add"      => $form_add->createView(),
                                    'date1' => $request->request->get('date1'),
                                    'date2' => $request->request->get('date2'),
                                    "tropical_wood"     => false,
                                    "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                                ]);
                            }
                        }

                        $fours = $repoFour->findAll();
                        if (count($fours) == 0) {
                            $manager->persist($fournisseur);
                        } else {
                            foreach ($fours as $four) {
                                $son_num_fact = $four->getNumeroFacture();
                                if ($numero_facture != $son_num_fact) {
                                    $manager->persist($fournisseur);
                                }
                            }
                        }
                        if (
                            $services->parseMyDate($data[$i][0]) == "erreur" ||
                            $services->parseMyDate($data[$i][5]) == "erreur" ||
                            $services->parseMyDate($data[$i][8]) == "erreur"
                        ) {
                            $error_fichier = $i;
                        }
                    }
                    // dd($fournisseur);               
                }
                if ($error_fichier == 0) {
                    $manager->flush();
                } else if ($error_fichier > 0) {
                    $text .= " 4. erreur dans le contenu <br>";
                    return $this->render('page/fournisseur.html.twig', [
                        "test" => $text,
                        "id"            => "li__fournisseur",
                        "hotel"         => $data_session['pseudo_hotel'],
                        "current_page"  => $data_session['current_page'],
                        "form_add"      => $form_add->createView(),
                        'date1' => $request->request->get('date1'),
                        'date2' => $request->request->get('date2'),
                        "tropical_wood"     => false,
                        "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                    ]);
                }
            }
            else{
                $text .= " 5. Absence de la feuille FOURNISSEURS <br>";
                return $this->render('page/fournisseur.html.twig', [
                    "id"            => "li__fournisseur",
                    "test" => $text,
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    'date2' => $request->request->get('date2'),
                    "tropical_wood"     => false,
                    "message" => "Le nom de feuille 'FOURNISSEURS' n'existe pas dans ce fichier",
                ]);
            }
        
            $text .= " 6. pas d'action <br>";
            return $this->render('page/fournisseur.html.twig', [
                "id"            => "li__fournisseur",
                "test" => $text,
                "hotel"         => $data_session['pseudo_hotel'],
                "current_page"  => $data_session['current_page'],
                "form_add"      => $form_add->createView(),
                'date1' => $request->request->get('date1'),
                'date2' => $request->request->get('date2'),
                "tropical_wood"     => false,
            ]);
            
        }

        else {
            if (($request->request->get('date1') != "") && ($request->request->get('date2') != "")) {
                return $this->render('page/fournisseur.html.twig', [
                    "id"            => "li__fournisseur",
                    "test" => "on a mentionner des dates",
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    'date2' => $request->request->get('date2'),
                    "tropical_wood"     => false,
                ]);
            } else if (($request->request->get('date1') == "") && ($request->request->get('date2') == "")) {
                //dd('tsisy e');
                return $this->render('page/fournisseur.html.twig', [
                    "id"            => "li__fournisseur",
                    "test" => "dates de tri vide form pas submitted",
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    'date2' => $request->request->get('date2'),
                    "tropical_wood"     => false,
                ]);
            }
        }

    }

    /**
     * @Route("/profile/{pseudo_hotel}/recap_fournisseur", name="recap_fournisseur")
     */
    public function recap_fournisseur(FournisseurRepository $repoFour, EntityManagerInterface $manager, Services $services, Request $request, SessionInterface $session, $pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        //dd($data_session);
        $data_session['current_page'] = "client_upload";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        
        $fours = $repoFour->findAll();
        $tab_fours_recap_p_av = [];
        $tab_fours_recap_p_ret = [];
        $today = new \DateTime();
        $today = date_create($today->format("d-m-Y"));
        $tab_echeance = [];
        $tab_par_echeance = [];
        foreach($fours as $four){
            foreach($four->getHotel() as $item){
                if($item->getPseudo() == $pseudo_hotel){
                    //array_push($tab_fours_recap, $four);
                    $son_echeance = $four->getEcheance();
                    if($son_echeance != null){
                        //dd($son_echeance);
                        if($son_echeance > $today){
                            $reste = $four->getReste();
                            if($reste > 0){
                                array_push($tab_fours_recap_p_av, $four);
                                if(!in_array($son_echeance, $tab_echeance)){
                                    array_push($tab_echeance, $son_echeance);
                                }
                            }
                        }
                        else if($son_echeance < $today){
                            // calcul du reste à payer
                            $reste = $four->getReste();
                            if($reste > 0){
                                // tsy mbol nahaloha 
                                array_push($tab_fours_recap_p_ret, $four);
                            }
                        }
                    }
                }
           }
        }
        
        for($i=0; $i< count($tab_echeance); $i++){
            $x = $repoFour->findByEcheances($tab_echeance[$i]);
            array_push($tab_par_echeance, $x);
        }
        //dd($tab_par_echeance);
        
        
        //dd($tab_fours_recap_p_av);
        return $this->render("page/recap_fournisseur.html.twig",[
            "id"            => "li__fournisseur",
            "hotel"         => $data_session['pseudo_hotel'],
            "current_page"  => $data_session['current_page'],
            'tab_fours_recap_p_av' => $tab_par_echeance,
            'tab_fours_recap_p_ret' => $tab_fours_recap_p_ret,
            'today' => $today,
            'tab_echeance' => $tab_echeance,
            "tropical_wood"     => false,
        ]);
    }
    
    public function parse_money($vola){
        $tab_vola = explode(",", $vola);
        $s = $tab_vola[0];
        for ($i = 1; $i < count($tab_vola); $i++) {
            $s .= $tab_vola[$i];
        }
        return doubleval($s);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/client_upload", name="client_upload")
     */
    public function client_upload(ClientUploadRepository $repoCup, EntityManagerInterface $manager, Services $services, Request $request, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "client_upload";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $form_add = $this->createform(FournisseurFileType::class);
        
        $form_add->handleRequest($request);
       
        if ($form_add->isSubmitted() && $form_add->isValid()) {
            $fichier = $form_add->get('fichier')->getData();
            //dd($fichier->getRealPath()); // tmp name
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();

            // on supprime tous les données présent
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            
            //dd($fileType);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            $sheetname = "DEBITEURS";
            $reader->setLoadSheetsOnly($sheetname);
            $spreadsheet = $reader->load($fichier->getRealPath());

            /** debut test */

            if ($spreadsheet->getSheetByName("DEBITEURS")) {
                $current_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $fours = $current_hotel->getClientUploads();
                foreach ($fours as $item) {
                    $manager->remove($item);
                    $manager->flush();
                }

                $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
                //dd($fileType);
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);
                $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
                $data = $spreadsheet->getSheetByName("DEBITEURS")->toArray();
                //dd($data);
                $error_fichier = 0;
                for ($i = 2; $i < count($data); $i++) {
                    $cup = new ClientUpload();
                    $annee = $data[$i][0];
                    $type_client = $data[$i][1];
                    $numero_facture = $data[$i][2];
                    $nom = $data[$i][3];
                    $personne_hebergee = $data[$i][4];
                    $montant = $data[$i][5];
                    $createdAtData = $data[$i][6];
                    $date_pmtData = $data[$i][8];
                    if ($createdAtData != "") {
                        //dd($createdAtData);
                        $createdAt_s = $services->parseMyDate($createdAtData);
                        if ($createdAt_s != "erreur") {
                            $date = date_create($createdAt_s);
                            $cup->setDate($date);
                        } else {
                            $error_fichier = $i;
                            return $this->render('page/client_upload.html.twig', [
                                "id"            => "li__client_upload",
                                "hotel"         => $data_session['pseudo_hotel'],
                                "current_page"  => $data_session['current_page'],
                                "form_add"      => $form_add->createView(),
                                'date1' => $request->request->get('date1'),
                                'date2' => $request->request->get('date2'),
                                "tropical_wood"     => false,
                                "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                            ]);
                        }
                    }
                    if ($date_pmtData != "") {
                        $date_pmt_s = $services->parseMyDate($date_pmtData);

                        if ($date_pmt_s != "erreur") {
                            $date_pmt = date_create($date_pmt_s);
                            $cup->setDatePmt($date_pmt);
                        } else {
                            $error_fichier = $i;
                            return $this->render('page/client_upload.html.twig', [
                                "id"            => "li__client_upload",
                                "hotel"         => $data_session['pseudo_hotel'],
                                "current_page"  => $data_session['current_page'],
                                "form_add"      => $form_add->createView(),
                                'date1' => $request->request->get('date1'),
                                "tropical_wood"     => false,
                                'date2' => $request->request->get('date2'),
                                "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                            ]);
                        }
                    }
                    $montant_paye = $data[$i][7];

                    //dd($createdAt);
                    $mode_pmt = $data[$i][9];
                    $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                    if ($numero_facture != "") {
                        $cup->setAnnee($annee);
                        $cup->setTypeClient($type_client);
                        $cup->setNumeroFacture($numero_facture);
                        $cup->setNom($nom);
                        $cup->setPersonneHebergee($personne_hebergee);
                        $cup->setMontant($montant);

                        $cup->setMontantPayer($montant_paye);

                        $cup->setModePmt($mode_pmt);
                        $cup->addHotel($hotel);
                        $cups = $repoCup->findAll();
                        if (count($cups) == 0) {
                            $manager->persist($cup);
                        } else {
                            foreach ($cups as $c) {
                                $son_num_fact = $c->getNumeroFacture();
                                if ($numero_facture != $son_num_fact) {
                                   
                                    $manager->persist($cup);
                                }
                            }
                        }
                        if (
                            $services->parseMyDate($data[$i][6]) == "erreur" ||
                            $services->parseMyDate($data[$i][8]) == "erreur"
                        ) {
                            $error_fichier = $i;
                        }
                    }
                }
                if ($error_fichier == 0) { 
                    $manager->flush();

                } else if ($error_fichier > 0) {
                    return $this->render('page/client_upload.html.twig', [
                        "id"            => "li__client_upload",
                        "hotel"         => $data_session['pseudo_hotel'],
                        "current_page"  => $data_session['current_page'],
                        "form_add"      => $form_add->createView(),
                        'date1' => $request->request->get('date1'),
                        'date2' => $request->request->get('date2'),
                        "tropical_wood"     => false,
                        "message" => "le format de date à la ligne " . ($error_fichier + 1) . " du fichier n'est pas valide <br> Seuls les formats comme 01/05/20 et 01/05/2020 sont acceptés",
                    ]);
                }
            }

            /**fin test */
            else {
               
                return $this->render('page/client_upload.html.twig', [
                    "id"            => "li__client_upload",
                    
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    'date2' => $request->request->get('date2'),
                    "tropical_wood"     => false,
                    "message" => "Le nom de feuille 'DEBITEURS' n'existe pas dans ce fichier",
                ]);
            }
           
            return $this->render('page/client_upload.html.twig', [
                "id"            => "li__client_upload",
                
                "hotel"         => $data_session['pseudo_hotel'],
                "current_page"  => $data_session['current_page'],
                "form_add"      => $form_add->createView(),
                'date1' => $request->request->get('date1'),
                "tropical_wood"     => false,
                'date2' => $request->request->get('date2'),
            ]);   
        }
        else{
            if (($request->request->get('date1') != "") && ($request->request->get('date2') != "")) {
                return $this->render('page/client_upload.html.twig', [
                    "id"            => "li__client_upload",
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    "tropical_wood"     => false,
                    'date2' => $request->request->get('date2'),
                ]);
            } else if (($request->request->get('date1') == "") && ($request->request->get('date2') == "")) {
                //dd('tsisy e');
                return $this->render('page/client_upload.html.twig', [
                    "id"            => "li__client_upload",
                    "hotel"         => $data_session['pseudo_hotel'],
                    "current_page"  => $data_session['current_page'],
                    "form_add"      => $form_add->createView(),
                    'date1' => $request->request->get('date1'),
                    'date2' => $request->request->get('date2'),
                    "tropical_wood"     => false,
                ]);
            }
        }
        return $this->render('page/client_upload.html.twig', [
            "id"            => "li__client_upload",
            "hotel"         => $data_session['pseudo_hotel'],
            "current_page"  => $data_session['current_page'],
            "form_add"      => $form_add->createView(),
            'date1' => $request->request->get('date1'),
            'date2' => $request->request->get('date2'),
            "tropical_wood"     => false,
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/fiche_hotel", name="fiche_hotel")
     */
    public function fiche_hotel(SessionInterface $session, $pseudo_hotel, FicheHotelRepository $repoFiche, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "fiche_hotel";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $id = $l_hotel->getId();
        // tous les fiche hotel 

        $fiches = $repoFiche->findAll();
        $x = new FicheHotel();
        foreach($fiches as $fiche){
            $son_hotel = $fiche->getHotel();
            $id_hotel = $son_hotel->getId();
            if($id == $id_hotel){
                $x = $fiche;
            }
            
        }

        $t = 0 ;
        $t += $x->getCPrestige();
        $t += $x->getSFamilliale();
        $t += $x->getCDeluxe();
        $t += $x->getSVip();

        //dd($t);
        // HotelRepository $repoHotel
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }

       else{
            return $this->render('page/fiche_hotel.html.twig', [
                "id" => "fiche_hotel",
                "fiche" => $x,
                "total" => $t,
                "tropical_wood"     => false,
                "hotel" => $data_session['pseudo_hotel'],
                "current_page" => $data_session['current_page']
            ]);
       }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/donnee_jour", name="donnee_jour")
     * @Route("/profile/{pseudo_hotel}/donnee_jour/{id}", name="donnee_jour_modif")
     * @Route("/profile/{pseudo_hotel}/add/donnee_jour/{val}", name="donnee_jour_add")
     * 
     */
    public function donnee_jour($val = null, DonneeDuJour $ddj = null, Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {
            $data_session = $session->get('hotel');
            //dd($data_session);
            if($data_session == null){
                return $this->redirectToRoute("app_logout");
            }
            $data_session['current_page'] = "donnee_jour";
            $data_session['pseudo_hotel'] = $pseudo_hotel;
            $today = new \DateTime();
            if($val != null){
                $val = explode('"', $val);
                $today = date_create($val[1]);
                //dd($today);
            }
            if(!$ddj){
                $ddj = new DonneeDuJour();
            }
            else if($ddj){
                $today = $ddj->getCreatedAt();
            }
            
            $form = $this->createForm(DonneeDuJourType::class, $ddj);
            
            // si le formulaire est soumis 
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {           
                $ddj = $form->getData();
                $createdAt  = $request->request->get('createdAt');
                $createdAt = date_create($createdAt);
                $ddj->setCreatedAt($createdAt);
                //dd($ddj);
                $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
                $hotel->addDonneeDuJour($ddj);
                $manager->persist($ddj);
                $manager->persist($hotel);
                $manager->flush();
                return $this->redirectToRoute('donnee_jour', ['pseudo_hotel' => $pseudo_hotel]);
            }
            // HotelRepository $repoHotel
            $user = $data_session['user'];
            $pos = $this->services->tester_droit($pseudo_hotel, $user, $reposHotel);
            if ($pos == "impossible") {
                return $this->render('/page/error.html.twig');
            } else {
                return $this->render('page/donnee_jour.html.twig', [
                    "id" => "li__donnee_du_jour",
                    "tropical_wood"     => false,
                    "form" => $form->createView(),
                    "hotel" => $data_session['pseudo_hotel'],
                    "current_page" => $data_session['current_page'],
                    "today" => $today->format("Y-m-d"),
                ]);
            }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_hebergement", name="h_hebergement")
     */
    public function h_hebergement(Request $request, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "h_hebergement";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $value_date1 = "";
        $value_date2 = "";
        $today = new \DateTime();
        if($request->request->count() > 0){
            $value_date1 = $request->request->get('date1');
            $value_date2 = $request->request->get('date2');
            //dd($request->request);
        }
        // HotelRepository $repoHotel
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
       else{
            return $this->render('page/h_hebergement.html.twig', [
                "hotel"             => $data_session['pseudo_hotel'],
                "tropical_wood"     => false,
                "current_page"      => $data_session['current_page'],
                "value_date1"       => $value_date1,
                "value_date2"       => $value_date2,
                "today"             => $today,   
            ]);
       }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_restaurant", name="h_restaurant")
     */
    public function h_restaurant(Request $request, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "h_restaurant";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        // HotelRepository $repoHotel
        $today = new \DateTime();
        $value_date1 = "";
        $value_date2 = "";
        if ($request->request->count() > 0) {
            $value_date1 = $request->request->get('date1');
            $value_date2 = $request->request->get('date2');
            //dd($request->request);
        }
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
       else{
            return $this->render('page/h_restaurant.html.twig', [
                "hotel"                 => $data_session['pseudo_hotel'],
                "tropical_wood"         => false,
                "current_page"          => $data_session['current_page'],
                "value_date1"           => $value_date1,
                "value_date2"           => $value_date2,
                "today"                 => $today, 
            ]);
       }
    }

    /**
     * @Route("/profile/{pseudo_hotel}/h_spa", name="h_spa")
     */
    public function h_spa(Request $request, SessionInterface $session, $pseudo_hotel, HotelRepository $repoHotel)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "h_spa";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        // HotelRepository $repoHotel
        $today = new \DateTime();
        $value_date1 = "";
        $value_date2 = "";
        if ($request->request->count() > 0) {
            $value_date1 = $request->request->get('date1');
            $value_date2 = $request->request->get('date2');
            //dd($request->request);
        }
        $user = $data_session['user'];
        $pos = $this->services->tester_droit($pseudo_hotel, $user, $repoHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        }
       else{
            return $this->render('page/h_spa.html.twig', [
                "hotel" => $data_session['pseudo_hotel'],
                "tropical_wood"     => false,
                "current_page"      => $data_session['current_page'],
                "value_date1"       => $value_date1,
                "value_date2"       => $value_date2,
                "today"             => $today, 
            ]);
       }
    }

    

    // navigation entre les hotels

    /**
     * @Route("/profile/royal_beach/{current_page}", name="royal_beach")
     */
    public function royal_beach($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "royal_beach";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], [
            'pseudo_hotel'      => $data_session['pseudo_hotel']
        ]);
    }

    /**
     * @Route("/profile/calypso/{current_page}", name="calypso")
     */
    public function calypso($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "calypso";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], [
            'pseudo_hotel' => $data_session['pseudo_hotel']
            ]);
    }

    /**
     * @Route("/profile/baobab_tree/{current_page}", name="baobab_tree")
     */
    public function baobab_tree($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "baobab_tree";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], [
            'pseudo_hotel' => $data_session['pseudo_hotel']
            ]);
    }

    /**
     * @Route("/profile/vanila_hotel/{current_page}", name="vanila_hotel")
     */
    public function vanila_hotel($current_page, SessionInterface $session)
    {
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "vanila_hotel";
        $data_session['current_page'] = $current_page;

        return $this->redirectToRoute($data_session['current_page'], [
            'pseudo_hotel' => $data_session['pseudo_hotel']
            ]);
    }

    
    /**
     * @Route("/profile/filtre/graph/heb_to/{pseudo_hotel}", name = "filtre_graph_heb_to")
     */
    public function filtre_graph_heb_to($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                

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
            } else {

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
                    // $tab_heb_to[$i] = floatval(($tab_heb_to[$i] / $tab_e[$i]));
                }


                $data = json_encode($tab_heb_to);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }

    



    /**
     * @Route("/profile/filtre/graph/heb_ca/{pseudo_hotel}", name = "filtre_graph_heb_ca")
     */
    public function filtre_graph_heb_ca($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                // les var pour les heb_to

                $tab_jour_heb_ca = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    //dd($donnee);
                    if ($donnee == $son_mois_createdAt) {

                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        //$val = floatval(str_replace(" ", "", $tab_jour_heb_ca[$num]));
                        $tab_jour_heb_ca[$num] = $d->getHebCa();
                        $tab_jour_heb_ca[$num] =  floatval(str_replace(' ', '', $tab_jour_heb_ca[$num])) ;
                        // $tab_jour_heb_ca[$num] = number_format($tab_jour_heb_ca[$num], 2);
                    }
                }

                $data = json_encode($tab_jour_heb_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {

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
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $eca_jan++;
                            $heb_ca_jan += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "02") {
                            $eca_fev++;
                            $heb_ca_fev += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "03") {
                            $eca_mars++;
                            $heb_ca_mars += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "04") {
                            $eca_avr++;
                            $heb_ca_avr += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "05") {
                            $eca_mai++;
                            $heb_ca_mai += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "06") {
                            $eca_juin++;
                            $heb_ca_juin += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "07") {
                            $eca_juil++;
                            $heb_ca_juil += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "08") {
                            $eca_aou++;
                            $heb_ca_aou += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "09") {
                            $eca_sep++;
                            $heb_ca_sep += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "10") {
                            $eca_oct++;
                            $heb_ca_oct += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "11") {
                            $eca_nov++;
                            $heb_ca_nov += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                        if ($son_mois_ca == "12") {
                            $eca_dec++;
                            $heb_ca_dec += floatval(str_replace(' ', '', $ddj->getHebCa()));
                        }
                    }
                }
                $tab_heb_ca = [$heb_ca_jan, $heb_ca_fev, $heb_ca_mars, $heb_ca_avr, $heb_ca_mai, $heb_ca_juin, $heb_ca_juil, $heb_ca_aou, $heb_ca_sep, $heb_ca_oct, $heb_ca_nov, $heb_ca_dec];
                $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
                for ($i = 0; $i < count($tab_eca); $i++) {
                    if ($tab_eca[$i] == 0) {
                        $tab_eca[$i] = 1;
                    }
                   
                    //$tab_heb_ca[$i] = $tab_heb_ca[$i] / $tab_eca[$i]; // / 10^6 car l'unité de graphe est le million
                    $tab_heb_ca[$i] = floatval(str_replace(' ', '', $tab_heb_ca[$i])) ;
                    // $tab_heb_ca[$i] = number_format($tab_heb_ca[$i], 2);
                }


                $data = json_encode($tab_heb_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

    }

    /**
     * @Route("/profile/filtre/graph/pax/{pseudo_hotel}", name = "filtre_graph_pax")
     */
    public function filtre_graph_pax($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, 
        Request $request, EntityManagerInterface $manager, 
        ClientRepository $repo, SessionInterface $session, 
        HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                // les var pour les heb_to

                $tab_jour_n_pax = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                $tab_jour_n_chambre = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    //dd($donnee);
                    if ($donnee == $son_mois_createdAt) {

                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        //$val = floatval(str_replace(" ", "", $tab_jour_pax[$num]));
                        $tab_jour_n_pax[$num] = $d->getNPaxHeb();
                        $tab_jour_n_chambre[$num] = $d->getNChambreOccupe();
                        $tab_jour_n_pax[$num] =  floatval(str_replace(' ', '', $tab_jour_n_pax[$num])) ;
                        $tab_jour_n_chambre[$num] =  floatval(str_replace(' ', '', $tab_jour_n_chambre[$num])) ;
                        
                    }
                }
                $retour = [
                    'tab_jour_n_pax' => $tab_jour_n_pax,
                    'tab_jour_n_chambre'=> $tab_jour_n_chambre
                ];

                $data = json_encode($retour);
               
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {

                $heb_n_pax_jan = 0;
                $heb_n_pax_fev = 0;
                $heb_n_pax_mars = 0;
                $heb_n_pax_avr = 0;
                $heb_n_pax_mai = 0;
                $heb_n_pax_juin = 0;
                $heb_n_pax_juil = 0;
                $heb_n_pax_aou = 0;
                $heb_n_pax_sep = 0;
                $heb_n_pax_oct = 0;
                $heb_n_pax_nov = 0;
                $heb_n_pax_dec = 0;

                // effectif pour la moyen 

                $en_pax_jan = 0;
                $en_pax_fev = 0;
                $en_pax_mars = 0;
                $en_pax_avr = 0;
                $en_pax_mai = 0;
                $en_pax_juin = 0;
                $en_pax_juil = 0;
                $en_pax_aou = 0;
                $en_pax_sep = 0;
                $en_pax_oct = 0;
                $en_pax_nov = 0;
                $en_pax_dec = 0;

                $heb_n_chambre_jan = 0;
                $heb_n_chambre_fev = 0;
                $heb_n_chambre_mars = 0;
                $heb_n_chambre_avr = 0;
                $heb_n_chambre_mai = 0;
                $heb_n_chambre_juin = 0;
                $heb_n_chambre_juil = 0;
                $heb_n_chambre_aou = 0;
                $heb_n_chambre_sep = 0;
                $heb_n_chambre_oct = 0;
                $heb_n_chambre_nov = 0;
                $heb_n_chambre_dec = 0;

                // effectif pour la moyen 

                $en_chambre_jan = 0;
                $en_chambre_fev = 0;
                $en_chambre_mars = 0;
                $en_chambre_avr = 0;
                $en_chambre_mai = 0;
                $en_chambre_juin = 0;
                $en_chambre_juil = 0;
                $en_chambre_aou = 0;
                $en_chambre_sep = 0;
                $en_chambre_oct = 0;
                $en_chambre_nov = 0;
                $en_chambre_dec = 0;

                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_pax = $son_createdAt->format("m");
                    $son_annee_pax = $son_createdAt->format("Y");
                    if ($son_annee_pax == $annee_actuel) {
                        if ($son_mois_pax == "01") {
                            $en_pax_jan++;
                            $en_chambre_jan++;
                            $heb_n_pax_jan += $ddj->getNPaxHeb();
                            $heb_n_chambre_jan += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "02") {
                            $en_pax_fev++;
                            $en_chambre_fev++;
                            $heb_n_pax_fev += $ddj->getNPaxHeb();
                            $heb_n_chambre_fev += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "03") {
                            $en_pax_mars++;
                            $en_chambre_mars++;
                            $heb_n_pax_mars += $ddj->getNPaxHeb();
                            $heb_n_chambre_mars += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "04") {
                            $en_pax_avr++;
                            $en_chambre_avr++;
                            $heb_n_pax_avr += $ddj->getNPaxHeb();
                            $heb_n_chambre_avr += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "05") {
                            $en_pax_mai++;
                            $en_chambre_mai++;
                            $heb_n_pax_mai += $ddj->getNPaxHeb();
                            $heb_n_chambre_mai += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "06") {
                            $en_pax_juin++;
                            $en_chambre_juin++;
                            $heb_n_pax_juin += $ddj->getNPaxHeb();
                            $heb_n_chambre_juin += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "07") {
                            $en_pax_juil++;
                            $en_chambre_juil++;
                            $heb_n_pax_juil += $ddj->getNPaxHeb();
                            $heb_n_chambre_juil += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "08") {
                            $en_pax_aou++;
                            $en_chambre_aou++;
                            $heb_n_pax_aou += $ddj->getNPaxHeb();
                            $heb_n_chambre_aou += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "09") {
                            $en_pax_sep++;
                            $en_chambre_sep++;
                            $heb_n_pax_sep += $ddj->getNPaxHeb();
                            $heb_n_chambre_sep += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "10") {
                            $en_pax_oct++;
                            $en_chambre_oct++;
                            $heb_n_pax_oct += $ddj->getNPaxHeb();
                            $heb_n_chambre_oct += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "11") {
                            $en_pax_nov++;
                            $en_chambre_nov++;
                            $heb_n_pax_nov += $ddj->getNPaxHeb();
                            $heb_n_chambre_nov += $ddj->getNChambreOccupe();
                        }
                        if ($son_mois_pax == "12") {
                            $en_pax_dec++;
                            $en_chambre_dec++;
                            $heb_n_pax_dec += $ddj->getNPaxHeb();
                            $heb_n_chambre_dec += $ddj->getNChambreOccupe();
                        }
                    }
                }

                $tab_heb_n_pax = [$heb_n_pax_jan, $heb_n_pax_fev, $heb_n_pax_mars, $heb_n_pax_avr, $heb_n_pax_mai, $heb_n_pax_juin, $heb_n_pax_juil, $heb_n_pax_aou, $heb_n_pax_sep, $heb_n_pax_oct, $heb_n_pax_nov, $heb_n_pax_dec];
                $tab_e_n_pax = [$en_pax_jan, $en_pax_fev, $en_pax_mars, $en_pax_avr, $en_pax_mai, $en_pax_juin, $en_pax_juil, $en_pax_aou, $en_pax_sep, $en_pax_oct, $en_pax_nov, $en_pax_dec];
                
                $tab_heb_n_chambre = [$heb_n_chambre_jan, $heb_n_chambre_fev, $heb_n_chambre_mars, $heb_n_chambre_avr, $heb_n_chambre_mai, $heb_n_chambre_juin, $heb_n_chambre_juil, $heb_n_chambre_aou, $heb_n_chambre_sep, $heb_n_chambre_oct, $heb_n_chambre_nov, $heb_n_chambre_dec];
                $tab_e_n_chambre = [$en_chambre_jan, $en_chambre_fev, $en_chambre_mars, $en_chambre_avr, $en_chambre_mai, $en_chambre_juin, $en_chambre_juil, $en_chambre_aou, $en_chambre_sep, $en_chambre_oct, $en_chambre_nov, $en_chambre_dec];
 
                for ($i = 0; $i < count($tab_e_n_pax); $i++) {
                    if ($tab_e_n_pax[$i] == 0) {
                        $tab_e_n_pax[$i] = 1;
                    }
                   
                    $tab_heb_n_pax[$i] = $tab_heb_n_pax[$i] ;
                    
                }

                for ($i = 0; $i < count($tab_e_n_chambre); $i++) {
                    if ($tab_e_n_chambre[$i] == 0) {
                        $tab_e_n_chambre[$i] = 1;
                    }
                   
                    $tab_heb_n_chambre[$i] = $tab_heb_n_chambre[$i] ;
                    
                }

                $retour = [
                    'tab_heb_n_pax' => $tab_heb_n_pax,
                    'tab_heb_n_chambre' => $tab_heb_n_chambre
                ];

                $data = json_encode($retour);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

    }

    /**
     * @Route("/profile/filtre/graph/res_ca/{pseudo_hotel}", name = "filtre_graph_res_ca")
     */
    public function filtre_graph_res_ca($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {


            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                $tab_jour_res_ca = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    //dd($donnee);
                    if ($donnee == $son_mois_createdAt) {

                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_res_ca[$num] = $d->getResCa();
                        $tab_jour_res_ca[$num] =  floatval(str_replace(' ', '', $tab_jour_res_ca[$num])) ;
                        // $tab_jour_res_ca[$num] = number_format($tab_jour_res_ca[$num], 2);
                    }
                }

                $data = json_encode($tab_jour_res_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {

                $res_ca_jan = 0;
                $res_ca_fev = 0;
                $res_ca_mars = 0;
                $res_ca_avr = 0;
                $res_ca_mai = 0;
                $res_ca_juin = 0;
                $res_ca_juil = 0;
                $res_ca_aou = 0;
                $res_ca_sep = 0;
                $res_ca_oct = 0;
                $res_ca_nov = 0;
                $res_ca_dec = 0;

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
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $eca_jan++;
                            $res_ca_jan += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "02") {
                            $eca_fev++;
                            $res_ca_fev += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "03") {
                            $eca_mars++;
                            $res_ca_mars += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "04") {
                            $eca_avr++;
                            $res_ca_avr += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "05") {
                            $eca_mai++;
                            $res_ca_mai += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "06") {
                            $eca_juin++;
                            $res_ca_juin += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "07") {
                            $eca_juil++;
                            $res_ca_juil += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "08") {
                            $eca_aou++;
                            $res_ca_aou += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "09") {
                            $eca_sep++;
                            $res_ca_sep += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "10") {
                            $eca_oct++;
                            $res_ca_oct += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "11") {
                            $eca_nov++;
                            $res_ca_nov += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                        if ($son_mois_ca == "12") {
                            $eca_dec++;
                            $res_ca_dec += floatval(str_replace(' ', '', $ddj->getResCa()));
                        }
                    }
                }
                $tab_res_ca = [$res_ca_jan, $res_ca_fev, $res_ca_mars, $res_ca_avr, $res_ca_mai, $res_ca_juin, $res_ca_juil, $res_ca_aou, $res_ca_sep, $res_ca_oct, $res_ca_nov, $res_ca_dec];
                $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
                for ($i = 0; $i < count($tab_eca); $i++) {
                    if ($tab_eca[$i] == 0) {
                        $tab_eca[$i] = 1;
                    }
                    //$tab_res_ca[$i] = $tab_res_ca[$i] / $tab_eca[$i]; // / 10^6 car l'unité de graphe est le million
                    $tab_res_ca[$i] = floatval(str_replace(' ', '', $tab_res_ca[$i])) ;
                    // $tab_res_ca[$i] = number_format($tab_res_ca[$i], 2);
                }


                $data = json_encode($tab_res_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

    }



    /**
     * @Route("/profile/filtre/graph/res_couvert/{pseudo_hotel}", name = "filtre_graph_res_couvert")
     */
    public function filtre_graph_res_couvert($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {


                $tab_jour_res_pd = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                $tab_jour_res_d = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                $tab_jour_res_di = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                $tab_jour_res_total = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                $tableau_all = [];
                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    if ($donnee == $son_mois_createdAt) {
                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_res_pd[$num] = $d->getResPDej();
                        $tab_jour_res_d[$num] = $d->getResDej();
                        $tab_jour_res_di[$num] = $d->getResDinner();
                        $x =  $tab_jour_res_pd[$num];
                        $x +=  $tab_jour_res_d[$num];
                        $x +=  $tab_jour_res_di[$num];
                        $tab_jour_res_total[$num] = $x;
                    }
                }

                array_push($tableau_all, $tab_jour_res_pd, $tab_jour_res_d, $tab_jour_res_di, $tab_jour_res_total);

                $data = json_encode($tableau_all);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
                

                $res_pd_jan = 0;
                $res_pd_fev = 0;
                $res_pd_mars = 0;
                $res_pd_avr = 0;
                $res_pd_mai = 0;
                $res_pd_juin = 0;
                $res_pd_juil = 0;
                $res_pd_aou = 0;
                $res_pd_sep = 0;
                $res_pd_oct = 0;
                $res_pd_nov = 0;
                $res_pd_dec = 0;

                // les res_d pour chaque mois

                $res_d_jan = 0;
                $res_d_fev = 0;
                $res_d_mars = 0;
                $res_d_avr = 0;
                $res_d_mai = 0;
                $res_d_juin = 0;
                $res_d_juil = 0;
                $res_d_aou = 0;
                $res_d_sep = 0;
                $res_d_oct = 0;
                $res_d_nov = 0;
                $res_d_dec = 0;

                // les res_d pour chaque mois

                $res_di_jan = 0;
                $res_di_fev = 0;
                $res_di_mars = 0;
                $res_di_avr = 0;
                $res_di_mai = 0;
                $res_di_juin = 0;
                $res_di_juil = 0;
                $res_di_aou = 0;
                $res_di_sep = 0;
                $res_di_oct = 0;
                $res_di_nov = 0;
                $res_di_dec = 0;


                // effectif pour la moyen rec_pd

                $epd_jan = 0;
                $epd_fev = 0;
                $epd_mars = 0;
                $epd_avr = 0;
                $epd_mai = 0;
                $epd_juin = 0;
                $epd_juil = 0;
                $epd_aou = 0;
                $epd_sep = 0;
                $epd_oct = 0;
                $epd_nov = 0;
                $epd_dec = 0;

                // effectif pour la moyen res_d

                $ed_jan = 0;
                $ed_fev = 0;
                $ed_mars = 0;
                $ed_avr = 0;
                $ed_mai = 0;
                $ed_juin = 0;
                $ed_juil = 0;
                $ed_aou = 0;
                $ed_sep = 0;
                $ed_oct = 0;
                $ed_nov = 0;
                $ed_dec = 0;

                // effectif pour la moyen res_di

                $edi_jan = 0;
                $edi_fev = 0;
                $edi_mars = 0;
                $edi_avr = 0;
                $edi_mai = 0;
                $edi_juin = 0;
                $edi_juil = 0;
                $edi_aou = 0;
                $edi_sep = 0;
                $edi_oct = 0;
                $edi_nov = 0;
                $edi_dec = 0;


                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $epd_jan++;
                            $res_pd_jan += $ddj->getResPDej();

                            $ed_jan++;
                            $res_d_jan += $ddj->getResDej();

                            $edi_jan++;
                            $res_di_jan += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "02") {
                            $epd_fev++;
                            $res_pd_fev += $ddj->getResPDej();

                            $ed_fev++;
                            $res_d_fev += $ddj->getResDej();

                            $edi_fev++;
                            $res_di_fev += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "03") {
                            $epd_mars++;
                            $res_pd_mars += $ddj->getResPDej();

                            $ed_mars++;
                            $res_d_mars += $ddj->getResDej();

                            $edi_mars++;
                            $res_di_mars += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "04") {
                            $epd_avr++;
                            $res_pd_avr += $ddj->getResPDej();

                            $ed_avr++;
                            $res_d_avr += $ddj->getResDej();

                            $edi_avr++;
                            $res_di_avr += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "05") {
                            $epd_mai++;
                            $res_pd_mai += $ddj->getResPDej();

                            $ed_mai++;
                            $res_d_mai += $ddj->getResDej();

                            $edi_mai++;
                            $res_di_mai += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "06") {
                            $epd_juin++;
                            $res_pd_juin += $ddj->getResPDej();

                            $ed_juin++;
                            $res_d_juin += $ddj->getResDej();

                            $edi_juin++;
                            $res_di_juin += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "07") {
                            $epd_juil++;
                            $res_pd_juil += $ddj->getResPDej();

                            $ed_juil++;
                            $res_d_juil += $ddj->getResDej();

                            $edi_juil++;
                            $res_di_juil += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "08") {
                            $epd_aou++;
                            $res_pd_aou += $ddj->getResPDej();

                            $ed_aou++;
                            $res_d_aou += $ddj->getResDej();

                            $edi_aou++;
                            $res_di_aou += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "09") {
                            $epd_sep++;
                            $res_pd_sep += $ddj->getResPDej();

                            $ed_sep++;
                            $res_d_sep += $ddj->getResDej();

                            $edi_sep++;
                            $res_di_sep += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "10") {
                            $epd_oct++;
                            $res_pd_oct += $ddj->getResPDej();

                            $ed_oct++;
                            $res_d_oct += $ddj->getResDej();

                            $edi_oct++;
                            $res_di_oct += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "11") {
                            $epd_nov++;
                            $res_pd_nov += $ddj->getResPDej();

                            $ed_nov++;
                            $res_d_nov += $ddj->getResDej();

                            $edi_nov++;
                            $res_di_nov += $ddj->getResDinner();
                        }
                        if ($son_mois_ca == "12") {
                            $epd_dec++;
                            $res_pd_dec += $ddj->getResPDej();

                            $ed_dec++;
                            $res_d_dec += $ddj->getResDej();

                            $edi_dec++;
                            $res_di_dec += $ddj->getResDinner();
                        }
                    }
                }
                
                $tab_res_pd = [$res_pd_jan, $res_pd_fev, $res_pd_mars, $res_pd_avr, $res_pd_mai, $res_pd_juin, $res_pd_juil, $res_pd_aou, $res_pd_sep, $res_pd_oct, $res_pd_nov, $res_pd_dec];
                $tab_epd = [$epd_jan, $epd_fev, $epd_mars, $epd_avr, $epd_mai, $epd_juin, $epd_juil, $epd_aou, $epd_sep, $epd_oct, $epd_nov, $epd_dec];
                for ($i = 0; $i < count($tab_epd); $i++) {
                    if ($tab_epd[$i] == 0) {
                        $tab_epd[$i] = 1;
                    }
                    $tab_res_pd[$i] = intval(($tab_res_pd[$i] / $tab_epd[$i]));
                }

                //dd($tab_res_d);

                $tab_res_d = [$res_d_jan, $res_d_fev, $res_d_mars, $res_d_avr, $res_d_mai, $res_d_juin, $res_d_juil, $res_d_aou, $res_d_sep, $res_d_oct, $res_d_nov, $res_d_dec];
                $tab_ed = [$ed_jan, $ed_fev, $ed_mars, $ed_avr, $ed_mai, $ed_juin, $ed_juil, $ed_aou, $ed_sep, $ed_oct, $ed_nov, $ed_dec];
                for ($i = 0; $i < count($tab_ed); $i++) {
                    if ($tab_ed[$i] == 0) {
                        $tab_ed[$i] = 1;
                    }
                    $tab_res_d[$i] = intval(($tab_res_d[$i] / $tab_ed[$i]));
                }

                //dd($tab_res_d);


                $tab_res_di = [$res_di_jan, $res_di_fev, $res_di_mars, $res_di_avr, $res_di_mai, $res_di_juin, $res_di_juil, $res_di_aou, $res_di_sep, $res_di_oct, $res_di_nov, $res_di_dec];
                $tab_edi = [$edi_jan, $edi_fev, $edi_mars, $edi_avr, $edi_mai, $edi_juin, $edi_juil, $edi_aou, $edi_sep, $edi_oct, $edi_nov, $edi_dec];
                for ($i = 0; $i < count($tab_edi); $i++) {
                    if ($tab_edi[$i] == 0) {
                        $tab_edi[$i] = 1;
                    }
                    $tab_res_di[$i] = intval(($tab_res_di[$i] / $tab_edi[$i]));
                }

                //dd($tab_res_di);

                // total 
                $tab_total = [];
                for ($i = 0; $i < 12; $i++) {
                    $x = 0;
                    $x += $tab_res_pd[$i];
                    $x += $tab_res_d[$i];
                    $x += $tab_res_di[$i];
                    array_push($tab_total, $x);
                }
                $tableau_all = [];
                //dd($tab_total);
                array_push($tableau_all, $tab_res_pd, $tab_res_d, $tab_res_di, $tab_total);

                $data = json_encode($tableau_all);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

    }


    /**
     * @Route("/profile/filtre/graph/spa_ca/{pseudo_hotel}", name = "filtre_graph_spa_ca")
     */
    public function filtre_graph_spa_ca($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {


            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                $tab_jour_res_ca = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    //dd($donnee);
                    if ($donnee == $son_mois_createdAt) {

                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_res_ca[$num] = $d->getSpaCa();
                        $tab_jour_res_ca[$num] =  floatval(str_replace(' ', '', $tab_jour_res_ca[$num])) ;
                        // $tab_jour_res_ca[$num] = number_format($tab_jour_res_ca[$num], 2);
                    }
                }

                $data = json_encode($tab_jour_res_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
        
                $res_ca_jan = 0;
                $res_ca_fev = 0;
                $res_ca_mars = 0;
                $res_ca_avr = 0;
                $res_ca_mai = 0;
                $res_ca_juin = 0;
                $res_ca_juil = 0;
                $res_ca_aou = 0;
                $res_ca_sep = 0;
                $res_ca_oct = 0;
                $res_ca_nov = 0;
                $res_ca_dec = 0;

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
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $eca_jan++;
                            $res_ca_jan += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "02") {
                            $eca_fev++;
                            $res_ca_fev += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "03") {
                            $eca_mars++;
                            $res_ca_mars += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "04") {
                            $eca_avr++;
                            $res_ca_avr += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "05") {
                            $eca_mai++;
                            $res_ca_mai += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "06") {
                            $eca_juin++;
                            $res_ca_juin += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "07") {
                            $eca_juil++;
                            $res_ca_juil += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "08") {
                            $eca_aou++;
                            $res_ca_aou += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "09") {
                            $eca_sep++;
                            $res_ca_sep += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "10") {
                            $eca_oct++;
                            $res_ca_oct += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "11") {
                            $eca_nov++;
                            $res_ca_nov += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                        if ($son_mois_ca == "12") {
                            $eca_dec++;
                            $res_ca_dec += floatval(str_replace(' ', '', $ddj->getSpaCa()));
                        }
                    }
                }
                $tab_res_ca = [$res_ca_jan, $res_ca_fev, $res_ca_mars, $res_ca_avr, $res_ca_mai, $res_ca_juin, $res_ca_juil, $res_ca_aou, $res_ca_sep, $res_ca_oct, $res_ca_nov, $res_ca_dec];
                $tab_eca = [$eca_jan, $eca_fev, $eca_mars, $eca_avr, $eca_mai, $eca_juin, $eca_juil, $eca_aou, $eca_sep, $eca_oct, $eca_nov, $eca_dec];
                for ($i = 0; $i < count($tab_eca); $i++) {
                    if ($tab_eca[$i] == 0) {
                        $tab_eca[$i] = 1;
                    }
                    //$tab_res_ca[$i] = $tab_res_ca[$i] / $tab_eca[$i]; // / 10^6 car l'unité de graphe est le million
                    $tab_res_ca[$i] = floatval(str_replace(' ', '', $tab_res_ca[$i])) ;
                    // $tab_res_ca[$i] = number_format($tab_res_ca[$i], 2);
                }


                $data = json_encode($tab_res_ca);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }

        
    }




    /**
     * @Route("/profile/filtre/graph/spa_na/{pseudo_hotel}", name = "filtre_graph_spa_na")
     */
    public function filtre_graph_spa_na($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                $tab_jour_spa_cu = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    if ($donnee == $son_mois_createdAt) {
                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_spa_cu[$num] = $d->getSpaNAbonne();
                    }
                }


                $data = json_encode($tab_jour_spa_cu);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
                

                $spa_cu_jan = 0;
                $spa_cu_fev = 0;
                $spa_cu_mars = 0;
                $spa_cu_avr = 0;
                $spa_cu_mai = 0;
                $spa_cu_juin = 0;
                $spa_cu_juil = 0;
                $spa_cu_aou = 0;
                $spa_cu_sep = 0;
                $spa_cu_oct = 0;
                $spa_cu_nov = 0;
                $spa_cu_dec = 0;

                // effectif pour la moyen 

                $ecu_jan = 0;
                $ecu_fev = 0;
                $ecu_mars = 0;
                $ecu_avr = 0;
                $ecu_mai = 0;
                $ecu_juin = 0;
                $ecu_juil = 0;
                $ecu_aou = 0;
                $ecu_sep = 0;
                $ecu_oct = 0;
                $ecu_nov = 0;
                $ecu_dec = 0;
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $ecu_jan++;
                            $spa_cu_jan += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "02") {
                            $ecu_fev++;
                            $spa_cu_fev += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "03") {
                            $ecu_mars++;
                            $spa_cu_mars += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "04") {
                            $ecu_avr++;
                            $spa_cu_avr += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "05") {
                            $ecu_mai++;
                            $spa_cu_mai += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "06") {
                            $ecu_juin++;
                            $spa_cu_juin += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "07") {
                            $ecu_juil++;
                            $spa_cu_juil += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "08") {
                            $ecu_aou++;
                            $spa_cu_aou += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "09") {
                            $ecu_sep++;
                            $spa_cu_sep += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "10") {
                            $ecu_oct++;
                            $spa_cu_oct += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "11") {
                            $ecu_nov++;
                            $spa_cu_nov += $ddj->getSpaNAbonne();
                        }
                        if ($son_mois_ca == "12") {
                            $ecu_dec++;
                            $spa_cu_dec += $ddj->getSpaNAbonne();
                        }
                    }
                }
                $tab_spa_cu = [$spa_cu_jan, $spa_cu_fev, $spa_cu_mars, $spa_cu_avr, $spa_cu_mai, $spa_cu_juin, $spa_cu_juil, $spa_cu_aou, $spa_cu_sep, $spa_cu_oct, $spa_cu_nov, $spa_cu_dec];
                $tab_ecu = [$ecu_jan, $ecu_fev, $ecu_mars, $ecu_avr, $ecu_mai, $ecu_juin, $ecu_juil, $ecu_aou, $ecu_sep, $ecu_oct, $ecu_nov, $ecu_dec];
                for ($i = 0; $i < count($tab_ecu); $i++) {
                    if ($tab_ecu[$i] == 0) {
                        $tab_ecu[$i] = 1;
                    }
                    // $tab_spa_cu[$i] = number_format(($tab_spa_cu[$i] / $tab_ecu[$i]), 2);
                    $tab_spa_cu[$i] = intval($tab_spa_cu[$i]);
                }


                $data = json_encode($tab_spa_cu);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }

    /**
     * @Route("/profile/filtre/graph/spa_cu/{pseudo_hotel}", name = "filtre_graph_spa_cu")
     */
    public function filtre_graph_spa_cu($pseudo_hotel, DonneeDuJourRepository $repoDoneeDJ, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $donnee = $request->get('data');
            $donnee_explode = explode("-", $donnee);
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $all_ddj = $repoDoneeDJ->findBy(['hotel' => $hotel]);
            if ($donnee_explode[0] != 'tous_les_mois') {

                // les var pour les heb_to

                $tab_jour_spa_cu = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

                $num = 0;
                foreach ($all_ddj as $d) {
                    $son_mois_createdAt = $d->getCreatedAt()->format('m-Y');
                    if ($donnee == $son_mois_createdAt) {
                        $son_num_jour = $d->getCreatedAt()->format('d');
                        $num = intval($son_num_jour) - 1;
                        $tab_jour_spa_cu[$num] = $d->getSpaCUnique();
                    }
                }


                $data = json_encode($tab_jour_spa_cu);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
              
                // les heb_to pour chaque mois

                $spa_cu_jan = 0;
                $spa_cu_fev = 0;
                $spa_cu_mars = 0;
                $spa_cu_avr = 0;
                $spa_cu_mai = 0;
                $spa_cu_juin = 0;
                $spa_cu_juil = 0;
                $spa_cu_aou = 0;
                $spa_cu_sep = 0;
                $spa_cu_oct = 0;
                $spa_cu_nov = 0;
                $spa_cu_dec = 0;

                // effectif pour la moyen 

                $ecu_jan = 0;
                $ecu_fev = 0;
                $ecu_mars = 0;
                $ecu_avr = 0;
                $ecu_mai = 0;
                $ecu_juin = 0;
                $ecu_juil = 0;
                $ecu_aou = 0;
                $ecu_sep = 0;
                $ecu_oct = 0;
                $ecu_nov = 0;
                $ecu_dec = 0;
                $annee_actuel = $donnee_explode[1];
                foreach ($all_ddj as $ddj) {
                    $son_createdAt = $ddj->getCreatedAt();
                    $son_mois_ca = $son_createdAt->format("m");
                    $son_annee_ca = $son_createdAt->format("Y");
                    if ($son_annee_ca == $annee_actuel) {
                        if ($son_mois_ca == "01") {
                            $ecu_jan++;
                            $spa_cu_jan += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "02") {
                            $ecu_fev++;
                            $spa_cu_fev += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "03") {
                            $ecu_mars++;
                            $spa_cu_mars += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "04") {
                            $ecu_avr++;
                            $spa_cu_avr += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "05") {
                            $ecu_mai++;
                            $spa_cu_mai += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "06") {
                            $ecu_juin++;
                            $spa_cu_juin += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "07") {
                            $ecu_juil++;
                            $spa_cu_juil += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "08") {
                            $ecu_aou++;
                            $spa_cu_aou += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "09") {
                            $ecu_sep++;
                            $spa_cu_sep += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "10") {
                            $ecu_oct++;
                            $spa_cu_oct += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "11") {
                            $ecu_nov++;
                            $spa_cu_nov += $ddj->getSpaCUnique();
                        }
                        if ($son_mois_ca == "12") {
                            $ecu_dec++;
                            $spa_cu_dec += $ddj->getSpaCUnique();
                        }
                    }
                }
                $tab_spa_cu = [$spa_cu_jan, $spa_cu_fev, $spa_cu_mars, $spa_cu_avr, $spa_cu_mai, $spa_cu_juin, $spa_cu_juil, $spa_cu_aou, $spa_cu_sep, $spa_cu_oct, $spa_cu_nov, $spa_cu_dec];
                $tab_ecu = [$ecu_jan, $ecu_fev, $ecu_mars, $ecu_avr, $ecu_mai, $ecu_juin, $ecu_juil, $ecu_aou, $ecu_sep, $ecu_oct, $ecu_nov, $ecu_dec];
                for ($i = 0; $i < count($tab_ecu); $i++) {
                    if ($tab_ecu[$i] == 0) {
                        $tab_ecu[$i] = 1;
                    }
                    // $tab_spa_cu[$i] = number_format(($tab_spa_cu[$i] / $tab_ecu[$i]), 2);
                    $tab_spa_cu[$i] = intval($tab_spa_cu[$i]);
                }

                $data = json_encode($tab_spa_cu);

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }
}
