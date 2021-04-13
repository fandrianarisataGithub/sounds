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

        $allAnnee = $this->tab_annee($pseudo_hotel);
        $taille_allAnnee = count($allAnnee);
        //dd($allAnnee);
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "cost";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        $today = new \DateTime();
        $annee = $today->format("Y");
        if($taille_allAnnee > 0){
            $annee = $allAnnee[$taille_allAnnee - 1];
        }
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
                        $tab_resto_value[$son_numero_mois]    = $resto ;
                        $tab_elec_value[$son_numero_mois]     = $elec ;
                        $tab_eau_value[$son_numero_mois]      = $eau ;
                        $tab_gasoil_value[$son_numero_mois]   = $gasoil ;
                        $tab_salaire_value[$son_numero_mois]  = $salaire ;

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
                'tab_annee'         => $allAnnee,
                "annee"             => $annee,
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
                "tropical_wood"     => false,
            ]);
        }
    }

    /**
     * @Route("/profile/annee_dm", name="tab_annee_dm")
     * 
     */
     public function tab_annee($pseudo_hotel):array{
       
        $repoDm = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
        $allDm = $repoDm->findAll();
        $allAnnee = [];
        foreach($allDm as $item){
            $son_pseudo_hotel = $item->getHotel()->getPseudo();
            if($pseudo_hotel == $son_pseudo_hotel){
                $t = explode("-", $item->getMois());
                $son_annee = $t[1];
                if(!in_array($son_annee,$allAnnee)){
                    array_push($allAnnee, $son_annee);
                }
            }
        }
        sort($allAnnee);
        return $allAnnee;
         
    }
    
    /**
     * @Route("/profile/filtre/graph/cost_montant/{pseudo_hotel}", name="filtre_cost_montant")
     */
    public function cost_montant(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $annee = $request->get('data');

            $tab_resto_value = [];
            $tab_elec_value = [];
            $tab_eau_value = [];
            $tab_gasoil_value = [];
            $tab_salaire_value = [];

            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            $Liste = [];
            $test = 0;
            if (count($all_dm) > 0) {
                $test++;
                foreach ($all_dm as $item) {
                    $resto = $services->no_space($item->getCostRestaurantValue());
                    $elec = $services->no_space($item->getCostElectriciteValue());
                    $eau = $services->no_space($item->getCostEauValue());
                    $gasoil = $services->no_space($item->getCostGasoilValue());
                    $salaire = $services->no_space($item->getSalaireBruteValue());

                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois                      = intVal($tab_explode[0]) - 1;
                        $tab_resto_value[$son_numero_mois]    = $resto ;
                        $tab_elec_value[$son_numero_mois]     = $elec ;
                        $tab_eau_value[$son_numero_mois]      = $eau ;
                        $tab_gasoil_value[$son_numero_mois]   = $gasoil ;
                        $tab_salaire_value[$son_numero_mois]  = $salaire ;
                    }
                }
            }
            
            ksort($tab_resto_value);
            ksort($tab_elec_value);
            ksort($tab_eau_value);
            ksort($tab_gasoil_value);
            ksort($tab_salaire_value);
            
            $Liste["tab_resto_value"] = $tab_resto_value;
            $Liste["tab_elec_value"] = $tab_elec_value;
            $Liste["tab_eau_value"] = $tab_eau_value;
            $Liste["tab_gasoil_value"] = $tab_gasoil_value;
            $Liste["tab_salaire_value"] = $tab_salaire_value;
            $data = json_encode($Liste);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/filtre/graph/cost_pourcent/{pseudo_hotel}", name="filtre_cost_pourcent")
     */
    public function cost_pourcent(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $annee = $request->get('data');

            $tab_resto_p = [];
            $tab_elec_p = [];
            $tab_eau_p = [];
            $tab_gasoil_p = [];
            $tab_salaire_p = [];

            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            $Liste = [];
            $test = 0;
            if (count($all_dm) > 0) {
                $test++;
                foreach ($all_dm as $item) {
                    $resto = $item->getCostRestaurantPourcent();
                    $elec = $item->getCostElectricitePourcent();
                    $eau = $item->getCostEauPourcent();
                    $gasoil = $item->getCostGasoilPourcent();
                    $salaire = $item->getSalaireBrutePourcent();

                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois                      = intVal($tab_explode[0]) - 1;
                        $tab_resto_p[$son_numero_mois]    = $resto;
                        $tab_elec_p[$son_numero_mois]     = $elec;
                        $tab_eau_p[$son_numero_mois]      = $eau;
                        $tab_gasoil_p[$son_numero_mois]   = $gasoil;
                        $tab_salaire_p[$son_numero_mois]  = $salaire;
                    }
                }
            }

            ksort($tab_resto_p);
            ksort($tab_elec_p);
            ksort($tab_eau_p);
            ksort($tab_gasoil_p);
            ksort($tab_salaire_p);

            $Liste["tab_resto_p"] = $tab_resto_p;
            $Liste["tab_elec_p"] = $tab_elec_p;
            $Liste["tab_eau_p"] = $tab_eau_p;
            $Liste["tab_gasoil_p"] = $tab_gasoil_p;
            $Liste["tab_salaire_p"] = $tab_salaire_p;
            $data = json_encode($Liste);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
}
