<?php

namespace App\Controller;
use App\Services\Services;
use App\Entity\DonneeMensuelle;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SqnController extends AbstractController
{
    /**
     * @Route("/profile/sqn/{pseudo_hotel}", name="sqn")
     */
    public function sqn(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {
        $allAnnee = $this->tab_annee($pseudo_hotel);
        $taille_allAnnee = count($allAnnee);
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "sqn";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        $today = new \DateTime();
        $annee = $today->format("Y");
        if ($taille_allAnnee > 0) {
            $annee = $allAnnee[$taille_allAnnee - 1];
        }
        $tab_interne = [];
        $tab_booking = [];
        $tab_tripadvisor = [];
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        } else {
            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            //dd($all_dm);
            if (count($all_dm) > 0) {
                foreach ($all_dm as $item) {
                    $interne = $item->getSqnInterne();
                    $booking = $item->getSqnBooking();
                    $tripadvisor = $item->getSqnTripadvisor();
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois = intVal($tab_explode[0]) - 1;
                        $tab_interne[$son_numero_mois] = $interne;
                        $tab_booking[$son_numero_mois] = $booking;
                        $tab_tripadvisor[$son_numero_mois] = $tripadvisor;
                    }
                }
            }
            ksort($tab_booking);
            ksort($tab_interne);
            ksort($tab_tripadvisor);
            return $this->render('sqn/sqn.html.twig', [
                "id"                    => "li__sqn",
                "hotel"                 => $data_session['pseudo_hotel'],
                "current_page"          => $data_session['current_page'],
                'tab_annee'             =>  $allAnnee,
                "annee"                 =>  $annee,
                'tab_interne'           => $tab_interne,
                'tab_booking'           => $tab_booking,
                'tab_tripadvisor'       => $tab_tripadvisor,
                "tropical_wood"     => false,
            ]);
        }
    }

    /**
     * @Route("/profile/filtre/graph/sqn/{pseudo_hotel}", name="filtre_sqn")
     */
    public function filtre_sqn(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $annee = $request->get('data');

            $sqn_interne = [];
            $sqn_booking = [];
            $sqn_tripadvisor  = [];

            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            $Liste = [];
            $test = 0;
            if (count($all_dm) > 0) {
                $test++;
                foreach ($all_dm as $item) {
                    $interne = $item->getSqnInterne();
                    $booking = $item->getSqnBooking();
                    $tripadvisor = $item->getSqnTripadvisor();
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois                      = intVal($tab_explode[0]) - 1;
                        $sqn_interne[$son_numero_mois]    = $interne;
                        $sqn_booking[$son_numero_mois]     = $booking;
                        $sqn_tripadvisor[$son_numero_mois]      = $tripadvisor;
                    }
                }
            }

            ksort($sqn_interne);
            ksort($sqn_booking);
            ksort($sqn_tripadvisor);

            $Liste["sqn_interne"] = $sqn_interne;
            $Liste["sqn_booking"] = $sqn_booking;
            $Liste["sqn_tripadvisor"] = $sqn_tripadvisor;
           
            $data = json_encode($Liste);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/annee_dm", name="tab_annee_dm")
     * 
     */
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
}
