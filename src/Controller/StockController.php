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

        $data_session = $session->get('hotel');
        $data_session['current_page'] = "stock";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $today = new \DateTime();
        $annee = $today->format('Y');
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
                        $tab[$son_numero_mois] = $stock / 1000000;
                    }
                }
            }
            ksort($tab);
            return $this->render('stock/stock.html.twig', [
                "id"                => "li__stock",
                "hotel"             => $data_session['pseudo_hotel'],
                "current_page"      => $data_session['current_page'],
                'tab_annee'         => $services->tab_annee(),
                'tab_stock'         => $tab,
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
            //dd($all_dm);
            $tab = [];
            if (count($all_dm) > 0) {
                foreach ($all_dm as $item) {
                    $stock = $services->no_space($item->getStock());
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois = intVal($tab_explode[0]) - 1;
                        $tab[$son_numero_mois] = $stock / 1000000;
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
}
