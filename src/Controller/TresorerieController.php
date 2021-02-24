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
     * @Route("/tresorerie", name="tresorerie")
    */
    public function tresorerie(
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager
    ): Response
    {
        $data_session = $session->get('hotel');
        return $this->render('tresorerie/tresorerie.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"                   => "li_tresoreriet"
        ]);
    }
}
