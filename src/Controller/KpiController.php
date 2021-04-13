<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\DonneeMensuelle;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class KpiController extends AbstractController
{
    /**
     * @Route("/profile/filtre/graph/kpi/{pseudo_hotel}", name="filtre_kpi")
     */
    public function filtre_sqn(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $annee = $request->get('data');

            $data_adr = [];
            $data_revp = [];
          
            $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
            $hotel = $reposHotel->findOneByPseudo($pseudo_hotel);
            $all_dm = $repoDM->findBy(['hotel' => $hotel]);
            $Liste = [];
            $test = 0;
            if (count($all_dm) > 0) {
                $test++;
                foreach ($all_dm as $item) {
                    $adr = str_replace(" ", "", $item->getKpiAdr());
                    $revp = str_replace(" ", "", $item->getKpiRevp());
                    
                    $son_mois = $item->getMois();
                    $tab_explode = explode("-", $son_mois);
                    $son_annee = $tab_explode[1];
                    if ($son_annee == $annee) {
                        $son_numero_mois               = intVal($tab_explode[0]) - 1;
                        $data_adr[$son_numero_mois]    = $adr;
                        $data_revp[$son_numero_mois]   = $revp;
                        
                    }
                }
            }

            ksort($data_adr);
            ksort($data_revp);

            $Liste["data_adr"] = $data_adr;
            $Liste["data_revp"] = $data_revp;
           
            $data = json_encode($Liste);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
}
