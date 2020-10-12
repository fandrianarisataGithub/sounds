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
        dd($tab_sans_doublant);
        // on cherche le max et le min des ann
        


        return $this->render('page/hebergement.html.twig', [
            "id" => "li__hebergement",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
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
