<?php

namespace App\Controller;

use App\Services\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/profile/tresorerie/recette", name="tresorerie_recette")
    */
    public function tresorerie_recette(
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager
    ): Response
    {
        $data_session = $session->get('hotel');
        return $this->render('tresorerie/tresorerie_recette.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet"
        ]);
    }
    /**
     * @Route("/profile/tresorerie/depense", name="tresorerie_depense")
     */
    public function tresorerie_depense(
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager
    ): Response {
        $data_session = $session->get('hotel');
        return $this->render('tresorerie/tresorerie_depense.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet"
        ]);
    }

    /**
     * @Route("/profile/tresorerie/formulaire/{type}", name="formulaire_tres")
     */
    public function tresorerie_form(
        $type,
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager
    ): Response {
        $data_session = $session->get('hotel');
        return $this->render('tresorerie/formulaire_tres.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "type"              => $type,
        ]);
    }
}
