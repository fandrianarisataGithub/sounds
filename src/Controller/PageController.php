<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DonneeDuJour;
use App\Form\DonneeDuJourType;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PageController extends AbstractController
{
    /**
     *@Route("/profile/{pseudo_hotel}", name="home")
     */
    public function home(SessionInterface $session, $pseudo_hotel)
    {

        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        //dd($data_session);
        return $this->render("page/donnee_jour.html.twig", [
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
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
    public function crj(SessionInterface $session, $pseudo_hotel)
    {
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "crj";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        return $this->render('page/crj.html.twig', [
            "id" => "li__compte_rendu",
            "hotel" => $data_session['pseudo_hotel'],
            "current_page" => $data_session['current_page']
        ]);
    }

    /**
     * @Route("/profile/{pseudo_hotel}/hebergement", name="hebergement")
     */
    public function hebergement($pseudo_hotel, Request $request, EntityManagerInterface $manager, ClientRepository $repo, SessionInterface $session)
    {
        $response = new Response();
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "hebergement";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if ($request->isXmlHttpRequest()) {

            $nom_client = $request->get('nom_client');
            $prenom_client = $request->get('prenom_client');
            $string_date_arrivee = $request->get('date_arrivee');
            $string_date_depart = $request->get('date_depart');

            if (!empty($string_date_arrivee) &&  !empty($string_date_depart) && !empty($nom_client)) {

                $format = "Y-m-d";

                $date_arrivee = \DateTime::createFromFormat($format, $string_date_arrivee);
                $date_depart = \DateTime::createFromFormat($format, $string_date_depart);

                // calcul de diff de deux date 
                $erreur = 0;
                if ($date_arrivee <= $date_depart) {

                    $diff = $date_arrivee->diff($date_depart)->days;
                    $data = "nom = " . $nom_client . " prenom = " . $prenom_client . "arr = " . $string_date_arrivee . " dep = " . $string_date_depart . " diff = " . $diff;

                    // insertion


                    $client = new Client();
                    $client->setNom($nom_client)
                        ->setPrenom($prenom_client)
                        ->setDateArrivee($date_arrivee)
                        ->setDateDepart($date_depart)
                        ->setDureeSejour($diff);

                    $manager->persist($client);
                    $manager->flush();

                    $data = json_encode("0"); // formater le résultat de la requête en json

                    $response->headers->set('Content-Type', 'application/json');
                    $response->setContent($data);

                    return $response;
                } else {
                    $erreur = -1;
                    $data = json_encode($erreur); // formater le résultat de la requête en json

                    $response->headers->set('Content-Type', 'application/json');
                    $response->setContent($data);

                    return $response;
                }
            } else {

                $data = json_encode("form vide"); // formater le résultat de la requête en json

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);

                return $response;
            }

            // format en date 



        }
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
    public function donnee_jour(Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session)
    {
        $ddj = new DonneeDuJour();
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "donnee_jour";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $form = $this->createForm(DonneeDuJourType::class, $ddj);
        // si le formulaire est soumis 

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
