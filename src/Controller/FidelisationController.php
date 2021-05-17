<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FidelisationController extends AbstractController
{
    /**
     * @Route("/profile/fidelisation/home/{pseudo_hotel}", name="fidelisation_home")
     */
    public function fidelisation_home(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/home.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

     /**
     * @Route("/profile/fidelisation/cardex/{pseudo_hotel}", name="fidelisation_cardex")
     */
    public function fidelisation_cardex(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/cardex.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

     /**
     * @Route("/profile/fidelisation/exclusif/{pseudo_hotel}", name="fidelisation_exclusif")
     */
    public function fidelisation_exclusif(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/exclusif.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

     /**
     * @Route("/profile/fidelisation/preferentiel/{pseudo_hotel}", name="fidelisation_preferentiel")
     */
    public function fidelisation_preferentiel(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/preferentiel.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

     /**
     * @Route("/profile/fidelisation/privilege/{pseudo_hotel}", name="fidelisation_privilege")
     */
    public function fidelisation_privilege(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/privilege.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

     /**
     * @Route("/profile/fidelisation/fiche_client/{pseudo_hotel}", name="fidelisation_fiche_client")
     */
    public function fidelisation_fiche_clent(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/fiche_client.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }
}
