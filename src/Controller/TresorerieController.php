<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/tresorerie", name="tresorerie")
     */
    public function index(): Response
    {
        return $this->render('tresorerie/index.html.twig', [
            'controller_name' => 'TresorerieController',
        ]);
    }
}
