<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function index(Request $request)
    {   
        if($request->request->count()>0){
            $nom = $request->request->get('nom');
            $date_a = $request->request->get('date_a');
            $date_d = $request->request->get('date_d');
            
            $date_a = new \DateTime($date_a);
            $date_d = new \DateTime($date_d);
           $interval = $date_a->diff($date_d);
           dd(date_create('13-02-2013'));
        }
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
