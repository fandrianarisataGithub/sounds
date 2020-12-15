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

class CostController extends AbstractController
{
    /**
     * @Route("/profile/cost/{pseudo_hotel}", name="cost")
     */
    public function cost(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {

        $data_session = $session->get('hotel');
        $data_session['current_page'] = "cost";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        $today = new \DateTime();
        $annee = $today->format('Y');
        $tab_resto_value = [];
        $tab_elec_value = [];
        $tab_eau_value = [];
        $tab_gasoil_value = [];
        $tab_salaire_value = [];

        $tab_resto_p = [];
        $tab_elec_p = [];
        $tab_eau_p = [];
        $tab_gasoil_p = [];
        $tab_salaire_p = [];
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        } else {
            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            //dd($all_dm);
            if (count($all_dm) > 0) {
                foreach ($all_dm as $item) {
                    $resto = $services->no_space($item->getCostRestaurantValue());
                    $elec = $services->no_space($item->getCostElectriciteValue());
                    $eau = $services->no_space($item->getCostEauValue());
                    $gasoil = $services->no_space($item->getCostGasoilValue());
                    $salaire = $services->no_space($item->getSalaireBruteValue());

                    $restoP = $item->getCostRestaurantPourcent();
                    $elecP = $item->getCostElectricitePourcent();
                    $eauP = $item->getCostEauPourcent();
                    $gasoilP = $item->getCostGasoilPourcent();
                    $salaireP = $item->getSalaireBrutePourcent();
                    
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois                = intVal($tab_explode[0]) - 1;
                        $tab_resto_value[$son_numero_mois]    = $resto / 1000000;
                        $tab_elec_value[$son_numero_mois]     = $elec / 1000000;
                        $tab_eau_value[$son_numero_mois]      = $eau / 1000000;
                        $tab_gasoil_value[$son_numero_mois]   = $gasoil / 1000000;
                        $tab_salaire_value[$son_numero_mois]  = $salaire / 1000000;

                        $tab_resto_p[$son_numero_mois]    = $restoP;
                        $tab_elec_p[$son_numero_mois]     = $elecP;
                        $tab_eau_p[$son_numero_mois]      = $eauP;
                        $tab_gasoil_p[$son_numero_mois]   = $gasoilP;
                        $tab_salaire_p[$son_numero_mois]  = $salaireP;


                    }
                }
            }
            ksort($tab_resto_value);
            ksort($tab_elec_value);
            ksort($tab_eau_value);
            ksort($tab_gasoil_value);
            ksort($tab_salaire_value);

            ksort($tab_resto_p);
            ksort($tab_elec_p);
            ksort($tab_eau_p);
            ksort($tab_gasoil_p);
            ksort($tab_salaire_p);
            return $this->render('cost/cost.html.twig', [
                "id"                => "li__cost",
                "hotel"             => $data_session['pseudo_hotel'],
                "current_page"      => $data_session['current_page'],
                'tab_annee'         => $services->tab_annee(),
                'tab_resto_value'         => $tab_resto_value,
                'tab_elec_value'          => $tab_elec_value,
                'tab_eau_value'           => $tab_eau_value,
                'tab_gasoil_value'        => $tab_gasoil_value,
                'tab_salaire_value'       => $tab_salaire_value,

                'tab_resto_p'         => $tab_resto_p,
                'tab_elec_p'          => $tab_elec_p,
                'tab_eau_p'           => $tab_eau_p,
                'tab_gasoil_p'        => $tab_gasoil_p,
                'tab_salaire_p'       => $tab_salaire_p,
            ]);
        }
    }
}