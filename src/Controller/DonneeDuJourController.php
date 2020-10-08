<?php

namespace App\Controller;

use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DonneeDuJourController extends AbstractController
{
    /**
     * @Route("/admin/add_donnee_djr", name="add_donnee_djr")
     */
    public function add_donnee_djr(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        
        if ($request->isXmlHttpRequest()) {
            
            // $heb_to = $request->get('heb_to');
            // $heb_ca = $request->get('heb_ca');
            // $res_n_couvert = $request->get('res_n_couvert');
            // $res_ca = $request->get('res_ca');
            // $res_p_dej = $request->get('res_p_dej');
            // $res_dej = $request->get('res_dej');
            // $res_dinner = $request->get('res_dinner');
            // $spa_ca = $request->get('spa_ca');
            // $spa_n_abonne = $request->get('spa_n_abonne');
            // $spa_c_unique = $request->get('spa_c_unique');
            // $crj_direction = $request->get('crj_direction');
            // $crj_service_rh = $request->get('crj_service_rh');
            // $crj_commercial = $request->get('crj_commercial');
            // $crj_comptable = $request->get('crj_comptable');
            // $crj_reception = $request->get('crj_reception');
            // $crj_restaurant = $request->get('crj_restaurant');
            // $crj_spa = $request->get('crj_spa');
            // $crj_s_technique = $request->get('crj_s_technique');
            // $crj_litiges = $request->get('crj_litiges');
            // $created_at = new \DateTime();
            // $pseudo_hotel = $request->get('pseudo_hotel');

            

            $data = json_encode("deleted");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
}
