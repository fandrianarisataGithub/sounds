<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Fidelisation;
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
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/profile/fidelisation/home/{pseudo_hotel}", name="fidelisation_home")
     */
    public function fidelisation_home(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        $clients = $this->getDoctrine()->getRepository(Client::class)->findAll();
        
        $repoFid = $this->getDoctrine()->getRepository(Fidelisation::class);
        $fidelisations = $repoFid->findAll([], ["id" => "ASC"]);
       
        return $this->render('fidelisation/home.html.twig', [
            'fidelisation'  => true,
            "fidelisations" => $fidelisations,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }

    /**
     * @Route("/profile/check_fidelisation/{id}", name ="check_fidelisation")
     */
    public function check_fidelisation(Request $request, Fidelisation $fidelisation): Response
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $html = '

            <form action="">
                <div class="form-group">
                    <label for="fid_limite_nuitee">Maximum de nuitée : </label>
                    <input type="number" id="fid_limite_nuitee" placeholder = "20 ou 35 ..." class="form-control" value="'. $fidelisation->getLimiteNuite() .'">
                </div>
                <div class="form-group">
                    <label for="fid_limite_ca">Maximum de CA : </label>
                    <input type="number" id="fid_limite_ca" placeholder = "montant en Ar" class="form-control" value="'. $fidelisation->getLimiteCa() .'">
                </div>
                <div class="form-group">
                    <button class="btn btn-warning btn-sm" id="modal_button_modif_fid" data-id ="'. $fidelisation->getid() .'" ><span>Enregistrer</span></button>
                </div>
            </form>

            ';

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        
        return $response;
    }

    /**
     * @Route("/profile/modify_fidelisation/{id}", name ="modify_fidelisation")
     */
    public function modify_fidelisation(Request $request, Fidelisation $fidelisation): Response
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $ln = $request->get('ln');
            $lc = $request->get('lc');
            
            $fidelisation->setLimiteNuite(intval($ln));
            $fidelisation->setLimiteCa($lc);

            $this->em->persist($fidelisation);
            $this->em->flush();

            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        
        return $response;
    }

     /**
     * @Route("/profile/fidelisation/cardex/{pseudo_hotel}", name="fidelisation_cardex")
     */
    public function fidelisation_cardex(Request $request, SessionInterface $session, $pseudo_hotel): Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
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
        if(!$data_session){
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
        if(!$data_session){
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
        if(!$data_session){
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
    
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        
        return $this->render('fidelisation/fiche_client.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
        ]);
    }
}
