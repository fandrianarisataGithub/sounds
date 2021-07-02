<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Customer;
use App\Entity\Fidelisation;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use App\Repository\VisitRepository;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FidelisationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FidelisationController extends AbstractController
{
    private $em;
    private $repoFid;
    private $repoCust;
    private $repoHotel;
    private $repoVisit;

    public function __construct(VisitRepository $repoVisit, CustomerRepository $repoCust,HotelRepository $repoHotel, EntityManagerInterface $em, FidelisationRepository $repoFid)
    {
        $this->em = $em;
        $this->repoFid = $repoFid;
        $this->repoCust = $repoCust;
        $this->repoVisit = $repoVisit;
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
        // tous les clients fidélisé
        //$datas = $this->repoCust->compteCustInFid();
        $datas = $this->repoCust->findallCustAndhisFidelisation();
        //dd($datas);
        // calcul dans la table visit

        $nbrNuitee = 0;
        $rev_global = 0;

        foreach($datas as $data){
            $his_visits = $data[0]->getVisits();
            foreach($his_visits as $visit){
                $rev_global = $rev_global + $visit->getMontant();
                $nbrNuitee = $nbrNuitee + $visit->getNbrNuitee();
            }
        }
        
        $fidelisations = $this->repoFid->findAll([], ["id" => "ASC"]);

        // Effectif visit groupe by provenance

        $effectif_provenance = json_encode($this->repoVisit->getEffectifByProvenance());
        return $this->render('fidelisation/home.html.twig', [
            'fidelisation'          => true,
            "fidelisations"         => $fidelisations,
            'hotel'                 => $pseudo_hotel,
            "datas"                 => $datas,
            "nbrNuitee"             => $nbrNuitee,
            "rev_global"            => $rev_global,
            "current_page"          => $data_session['current_page'],
            "effectif_provenance"   => $effectif_provenance
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
        // tous les clients cardex DISTINCT, total prix_total et nombre de clients
        $fidelisation = $this->repoFid->findOneBy(['nom' => 'cardex']);
        $customers = $fidelisation->getCustomers();
        $ca = 0;
        $nuitee = 0;
        foreach($customers as $customer){
           
            $visits = $customer->getVisits();
            foreach($visits as $visit){
                $ca += $visit->getMontant();
                $nuitee += $visit->getNbrNuitee();
            }
        }
        return $this->render('fidelisation/cardex.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"  => $data_session['current_page'],
            "customers"     => $customers,
            "ca"            => $ca,
            "nuitee"        => $nuitee    
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
        $fidelisation = $this->repoFid->findOneBy(['nom' => 'exclusif']);
        $customers = $fidelisation->getCustomers();
        //dd($customers);
        $ca = 0;
        $nuitee = 0;
        foreach($customers as $customer){
           
            $visits = $customer->getVisits();
            foreach($visits as $visit){
                $ca += $visit->getMontant();
                $nuitee += $visit->getNbrNuitee();
            }
        }
        return $this->render('fidelisation/exclusif.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "customers"     => $customers,
            "ca"            => $ca,
            "nuitee"        => $nuitee   
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
        $fidelisation = $this->repoFid->findOneBy(['nom' => 'preferentiel']);
        $customers = $fidelisation->getCustomers();
        $ca = 0;
        $nuitee = 0;
        foreach($customers as $customer){
           
            $visits = $customer->getVisits();
            foreach($visits as $visit){
                $ca += $visit->getMontant();
                $nuitee += $visit->getNbrNuitee();
            }
        }
        return $this->render('fidelisation/preferentiel.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "customers"     => $customers,
            "ca"            => $ca,
            "nuitee"        => $nuitee   
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
        $fidelisation = $this->repoFid->findOneBy(['nom' => 'privilege']);
        $customers = $fidelisation->getCustomers();
        $ca = 0;
        $nuitee = 0;
        foreach($customers as $customer){
           
            $visits = $customer->getVisits();
            foreach($visits as $visit){
                $ca += $visit->getMontant();
                $nuitee += $visit->getNbrNuitee();
            }
        }
        return $this->render('fidelisation/privilege.html.twig', [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "customers"     => $customers,
            "ca"            => $ca,
            "nuitee"        => $nuitee   
        ]);
    }
    
     /**
     * @Route("/profile/fidelisation/fiche_client/{pseudo_hotel}/{id}", name="fidelisation_fiche_client")
     */
    public function fidelisation_fiche_client(Request $request, SessionInterface $session, $pseudo_hotel, ?Customer $customer): Response
    {
        $data_session = $session->get('hotel');
    
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        $fidelisation = $customer->getFidelisation();
        //dd($fidelisation->getNom());
        $visits = $customer->getVisits();
        // calcul chiffre d'aff et nuitée
        $ca = 0;
        $nuitee = 0;
        foreach($visits as $visit){
            $ca += $visit->getMontant();
            $nuitee += $visit->getNbrNuitee();
        }
        //dd($fidelisation);
        return $this->render('fidelisation/fiche_client.html.twig', [
            'fidelisation'      => true,
            'hotel'             => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "visits"            => $visits,
            "fidelisation"      => $fidelisation,
            'ca'                => $ca,
            'nuitee'            => $nuitee,
            'customer'          => $customer      
        ]);
    }
    /**
     * @Route("/profile/client_OTA/{pseudo_hotel}", name="client_OTA")
     */
    public function client_OTA(Request $request, SessionInterface $session, $pseudo_hotel) :Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        $datas = $this->repoVisit->findallCustAndhisFidelisationAndTypeVisit("OTA");
        //dd($datas);
        $ca = 0;
        $nuitee = 0;
        foreach($datas as $visit){
            $ca += floatval($visit->getMontant());
            $nuitee += intval($visit->getNbrNuitee());
        }

        // effectif par source 

        $effectif = json_encode($this->repoVisit->getEffectifByProvOTABySource());
        
        return $this->render("fidelisation/client/client_OTA.html.twig", [
            'fidelisation'      => true,
            'hotel'             => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "datas"             => $datas,
            "ca"                => $ca,
            "nuitee"            => $nuitee,
            "effectif"          => $effectif
        ]);
    }
    /**
     * @Route("/profile/client_CORPO/{pseudo_hotel}", name="client_CORPO")
     */
    public function client_CORPO(Request $request, SessionInterface $session, $pseudo_hotel) :Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        
        $datas = $this->repoVisit->findallCustAndhisFidelisationAndTypeVisit("CORPO");
        //dd($datas);
        $ca = 0;
        $nuitee = 0;
        
        foreach($datas as $visit){
            $ca += floatval($visit->getMontant());
            $nuitee += intval($visit->getNbrNuitee());
        }

        // effectif par source 

        // $effectif = json_encode($this->repoVisit->getEffectifBySource());
        return $this->render("fidelisation/client/client_CORPO.html.twig", [
            'fidelisation'      => true,
            'hotel'             => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "datas"             => $datas,
            "nuitee"            => $nuitee,
            "ca"                => $ca
        ]);
    }
    /**
     * @Route("/profile/client_TOA/{pseudo_hotel}", name="client_TOA")
     */
    public function client_TOA(Request $request, SessionInterface $session, $pseudo_hotel) :Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        $datas = $this->repoVisit->findallCustAndhisFidelisationAndTypeVisit("TOA");
        //dd($datas);
        $ca = 0;
        $nuitee = 0;
        
        foreach($datas as $visit){
            $ca += floatval($visit->getMontant());
            $nuitee += intval($visit->getNbrNuitee());
        }
        
        
        return $this->render("fidelisation/client/client_TOA.html.twig", [
            'fidelisation'  => true,
            'hotel'         => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "datas"             => $datas,
            "nuitee"            => $nuitee,
            "ca"                => $ca
        ]);
    }
    /**
     * @Route("/profile/client_DIRECT/{pseudo_hotel}", name="client_DIRECT")
     */
    public function client_DIRECT(Request $request, SessionInterface $session, $pseudo_hotel) :Response
    {
        $data_session = $session->get('hotel');
        if(!$data_session){
            return $this->redirectToRoute("app_logout");
        }
        $datas = $this->repoVisit->findallCustAndhisFidelisationAndTypeVisit("DIRECT");
        //dd($datas);
        $ca = 0;
        $nuitee = 0;
        
        foreach($datas as $visit){
            $ca += floatval($visit->getMontant());
            $nuitee += intval($visit->getNbrNuitee());
        }
        
        $effectif = json_encode($this->repoVisit->getEffectifByProvDirectBySource());
        return $this->render("fidelisation/client/client_DIRECT.html.twig", [
            'fidelisation'      => true,
            'hotel'             => $pseudo_hotel,
            "current_page"      => $data_session['current_page'],
            "datas"             => $datas,
            "nuitee"            => $nuitee,
            "ca"                => $ca,
            "effectif"          => $effectif
        ]);
    }
}
