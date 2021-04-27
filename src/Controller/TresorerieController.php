<?php

namespace App\Controller;

use App\Entity\Tresorerie;
use App\Services\Services;
use App\Entity\TresorerieDepense;
use App\Entity\TresorerieRecette;
use App\Entity\CategoryTresorerie;
use App\Form\TresorerieDepenseType;
use App\Form\TresorerieRecetteType;
use App\Entity\SousCategorieTresorerie;
use App\Repository\TresorerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TresorerieDepenseRepository;
use App\Repository\TresorerieRecetteRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryTresorerieRepository;
use App\Repository\SousCategorieTresorerieRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/profile/tresorerie/recette", name="tresorerie_recette")
    */
    public function tresorerie(
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager, 
        CategoryTresorerieRepository $repoCate
    ): Response
    {
        $data_session = $session->get('hotel');
        
        
        $data_session = $session->get('hotel');
       
        $liste_cate = $repoCate->findAll();
        return $this->render('tresorerie/tresorerie_aff.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "liste_cate"        => $liste_cate
        ]);
    }
    /**
     * @Route("/check_his_sous_category", name = "check_his_sous_category")
     */
    public function check_his_sous_category(Request $request, 
        SousCategorieTresorerieRepository $repoSousCate, 
        CategoryTresorerieRepository $repoCate)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $nom = $request->get('nom');
            $cate = $repoCate->findOneByNom($nom);
            $liste_sous_cate = [];
            foreach($cate->getSousCategorieTresoreries() as $item){
                array_push($liste_sous_cate, $item->getNom());
            }

            $data = json_encode($liste_sous_cate);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/profile/add_tres", name = "add_tres")
     */
    public function add_tres(Request $request, EntityManagerInterface $manager, TresorerieRepository $repoTres)
    {
        $response = new Response();
        $total_tres = count($repoTres->findAll());
        if($request->isXmlHttpRequest()){
            $tresorerie = new Tresorerie();
           
            $choice = $request->get('choice');
            $date = date_create($request->get('date'));
            $designation = $request->get('designation');
            $mode_pmt = $request->get('mode_pmt');
            $compte = $request->get('compte_b');
            $monnaie = $request->get('monnaie');
            $categorie = $request->get('categorie'); // categorie_recette
            $categorie_recette = $request->get('categorie_recette');
            $sous_categorie = $request->get('sous_categorie');
            
            // montant (dec,enc)
            // on hydrate tresorerie
            $tresorerie->setDatePaiment($date);
            $tresorerie->setDesignation($designation);
            $tresorerie->setModePaiement($mode_pmt);
            $tresorerie->setCompte($compte);
            $tresorerie->setMonnaie($monnaie);
            
            $tresorerie->setTypeFlux($choice);
            $tresorerie->setNumSage($request->get("sage"));
            if($choice === "encaissement"){
                $tresorerie->setCategorie($categorie_recette);
                $tresorerie->setEncaissement(str_replace(" ", "", $request->get("encaissement_montant")));
                $tresorerie->setIdPro($request->get("id_pro"));
                $tresorerie->setClient($request->get('client'));
            }
            if($choice === "decaissement"){
                $tresorerie->setCategorie($categorie);
                $tresorerie->setSousCategorie($sous_categorie);
                $tresorerie->setDecaissement(str_replace(" ", "", $request->get("decaissement_montant")));
                $tresorerie->setPrestataire($request->get('prestataire'));
            }
            $manager->persist($tresorerie);
            $manager->flush();
            $total_tres_after = count($repoTres->findAll());
            if($total_tres < $total_tres_after){
                $data = "ok";
                $data = json_encode($data);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
            }if($total_tres >= $total_tres_after){
                $data = "error";
                $data = json_encode($data);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
            }
            
        }
        return $response;
    }

    /**
     * @Route("/profile/search_tres" , name="search_tres")
    */
    public function search_tres(Request $request, TresorerieRepository $repoTres)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $select = $request->get('select');
            $input = $request->get('input');
           
            $html = "";
            $tresoreries = [];
            if($select == "Prestataire"){
                
                $tresoreries = $repoTres->findLikePrestataire($input);
            }
            else{
                if($select == "Client"){
                    
                    $tresoreries = $repoTres->findLikeClient($input);
                }
                if($select == "ID Pro"){
                    
                    $tresoreries = $repoTres->findLikeIdPro($input);
                }
                if($select == "Numero Sage"){
                  
                    $tresoreries = $repoTres->findLikeSage($input);
                }
            }
            foreach($tresoreries as $tresorerie){
                $html .= '
                <div class="t_body_row sous_tab_body t_body_tres">
                    <div class="tres_des">
                        <span>'. $tresorerie->getDesignation() .'</span>
                    </div>
                    <div class="tres_date">
                        <span>' . $tresorerie->getDatePaiment()->format("d-m-Y") .'</span>
                    </div>
                    <div class="tres_mode_p">
                        <span> ' . $tresorerie->getModePaiement() . '</span>
                    </div>
                    <div class="tres_compte_b">
                        <span>' . $tresorerie->getCompte() . '</span>
                    </div>
                    <div class="tres_compte_b">
                        <span class="montant">' . $tresorerie->getEncaissement() .'</span>
                    </div>
                    <div class="tres_compte_b">
                        <span class="montant">' . $tresorerie->getDecaissement() .'</span>
                    </div>
                    <div class="tres_monnaie">
                        <span>' . $tresorerie->getMonnaie() . '</span>
                    </div>
                    <div class="tres_monnaie">
                        <span> ' . $tresorerie->getTypeFlux() . '</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $tresorerie->getCategorie() . '</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $tresorerie->getSousCategorie() .'</span>
                    </div>
                    <div class="tres_idPro">
                        <span> '. $tresorerie->getIdPro() .'</span>
                    </div>
                    <div class=" tres_client">
                        <span> ' . $tresorerie->getClient() .'</span>
                    </div>
                    <div class="tres_sage">
                        <span> '. $tresorerie->getNumSage() . '</span>
                    </div>
                    <div class=" tres_client">
                        <span> ' . $tresorerie->getPrestataire() .'</span>
                    </div>
                </div>               
                ';
            }
            
            $data = $html;
            $data = json_encode($data);
            $response->headers->set('content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/profile/lister_data_tresorerie", name ="lister_data_tresorerie")
     */
    public function lister_data_tresorerie(Request $request,TresorerieRepository $repoTres)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');   //key_flux
            $key_flux = $request->get('key_flux');
            $html  = '';
            $tresoreries = [];
            if($date1 != "" || $date2 != ""){
                $tresoreries = $repoTres->find_between($date1, $date2, $key_flux);   
            }
            else{
                
                if($key_flux == "all"){
                    $tresoreries = $repoTres->findAll();
                }
                else if($key_flux == "encaissement"){
                    $tresoreries = $repoTres->findBy(["type_flux" => "encaissement"]);
                }
                else if($key_flux == "decaissement"){
                    $tresoreries = $repoTres->findBy(["type_flux" => "decaissement"]);
                }
            }
            foreach($tresoreries as $tresorerie){
                $html .='
                <div class="t_body_row sous_tab_body t_body_tres">
                    <div class="tres_des">
                        <span>'. $tresorerie->getDesignation() .'</span>
                    </div>
                    <div class="tres_date">
                        <span>' . $tresorerie->getDatePaiment()->format("d-m-Y") .'</span>
                    </div>
                    <div class="tres_mode_p">
                        <span> ' . $tresorerie->getModePaiement() .'</span>
                    </div>
                    <div class="tres_compte_b">
                        <span>' . $tresorerie->getCompte() . '</span>
                    </div>
                    <div class="tres_compte_b">
                        <span class="montant">' . $tresorerie->getEncaissement() .'</span>
                    </div>
                    <div class="tres_compte_b">
                        <span class="montant">' . $tresorerie->getDecaissement() .'</span>
                    </div>
                    <div class="tres_monnaie">
                        <span> ' . $tresorerie->getMonnaie() .'</span>
                    </div>
                    <div class="tres_monnaie">
                        <span> ' . $tresorerie->getTypeFlux() .'</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $tresorerie->getCategorie() .'</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $tresorerie->getSousCategorie() .'</span>
                    </div>
                    <div class="tres_idPro">
                        <span> '. $tresorerie->getIdPro() .'</span>
                    </div>
                    <div class=" tres_client">
                        <span> ' . $tresorerie->getClient() .'</span>
                    </div>
                    <div class="tres_sage">
                        <span> '. $tresorerie->getNumSage() . '</span>
                    </div>
                    <div class=" tres_client">
                        <span> ' . $tresorerie->getPrestataire() .'</span>
                    </div>
                </div>               
                ';
            }


            $data = $html;
            $data = json_encode($data);
            $response->headers->set('content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }
}
