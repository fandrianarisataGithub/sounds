<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Repository\CustomerRepository;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PrestationController extends AbstractController
{
    /**
     * @Route("/profile/add_prestation", name="add_prestation")
     */
    public function add_prestation(Request $request, CustomerRepository $repoCust, PrestationRepository $repoPresta, EntityManagerInterface $manager): Response
    {
        $response = new Response();
        $data = "";
        
        if($request->isXmlHttpRequest()){
            // $nom = $request->get('nom');
            // $quantity = $request->get('quantity');
            // $date = $request->get('createdAt');
            // $pax = $request->get('pax');
            
            // if(!empty($nom) && !empty($quantity) && !empty($pax) && !empty($date)){
            //     $createdAt = date_create($request->get('createdAt'));
            //     $customer_id = $request->get('customer_id');
            //     $customer = $repoCust->find($customer_id);

            //     $presta = new Prestation();
            //     $presta->setNom($nom);
            //     $presta->setQuantity($quantity);
            //     $presta->setCreatedAt($createdAt);
            //     $presta->setCustomer($customer);
            //     $presta->setPax($pax);
            //     $manager->persist($presta);
            //     $manager->flush();
            //     $data = "ok";
            // }else{
            //     $data = "Veuillez renseigner tous les champs";
            // }
            $data = "zay";

        }
        
        //dd($data);

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent($data);

        // end client insertion in fidelisation

        return $response;
        
    }
}
