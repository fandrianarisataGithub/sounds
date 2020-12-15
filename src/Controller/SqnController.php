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
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "sqn";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        $today = new \DateTime();
        $annee = $today->format('Y');
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
                'tab_annee'             => $services->tab_annee(),
                'tab_interne'           => $tab_interne,
                'tab_booking'           => $tab_booking,
                'tab_tripadvisor'       => $tab_tripadvisor,
            ]);
        }
    }
}
