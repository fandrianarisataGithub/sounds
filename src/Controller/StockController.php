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

class StockController extends AbstractController
{
    /**
     * @Route("/profile/stock/{pseudo_hotel}", name="stock")
     */
    public function stock(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {
        $allAnnee = $this->tab_annee($pseudo_hotel);
        $taille_allAnnee = count($allAnnee);
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['current_page'] = "stock";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $today = new \DateTime();
        $annee = $today->format("Y");
        if ($taille_allAnnee > 0) {
            $annee = $allAnnee[$taille_allAnnee - 1];
        }
        $tab = [];
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        } else {
            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel'=>$hotel]);
            //dd($all_dm);
            if(count($all_dm)>0){
                foreach ($all_dm as $item) {
                    $stock = $services->no_space($item->getStock());
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois = intVal($tab_explode[0]) - 1;
                        $tab[$son_numero_mois] = $stock;
                    }
                }
            }
            ksort($tab);

            //dd($tab);
            
            //rsort($allAnnee);
            return $this->render('stock/stock.html.twig', [
                "id"                => "li__stock",
                "hotel"             => $data_session['pseudo_hotel'],
                "current_page"      => $data_session['current_page'],
                'tab_annee'         =>  $allAnnee,
                "annee"             =>  $annee,
                'tab_stock'         => $tab,
                'current_year'      => $annee,
                "tropical_wood"     => false,
            ]);
        }
    }
    /**
     * @Route("/profile/filtre/graph/stock/{pseudo_hotel}", name = "stock_filtre")
     */
    public function stock_filtre(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $annee = $request->get('data');
            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            $tab = [];
            if (count($all_dm) > 0) {
                foreach ($all_dm as $item) {
                    $stock = $services->no_space($item->getStock());
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois = intVal($tab_explode[0]) - 1;
                        $tab[$son_numero_mois] = $stock;
                    }
                }
            }
        
            ksort($tab);
            $data = json_encode($tab);
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
