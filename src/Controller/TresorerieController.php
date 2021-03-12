<?php

namespace App\Controller;

use App\Entity\Tresorerie;
use App\Services\Services;
use App\Entity\TresorerieDepense;
use App\Entity\TresorerieRecette;
use App\Form\TresorerieDepenseType;
use App\Form\TresorerieRecetteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TresorerieDepenseRepository;
use App\Repository\TresorerieRecetteRepository;
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
        EntityManagerInterface $manager, TresorerieRecetteRepository $repoRecette, TresorerieDepenseRepository $repoDepense
    ): Response
    {
        $data_session = $session->get('hotel');
        $depenses = $repoDepense->findAll();
        
        $data_session = $session->get('hotel');
        $recettes = $repoRecette->findAll();
     
        return $this->render('tresorerie/tresorerie_aff.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "recettes"          => $recettes,
            "depenses"          => $depenses
        ]);
    }
    /**
     * @Route("/profile/tresorerie/depense", name="tresorerie_depense")
     */
    public function tresorerie_depense(
        SessionInterface $session,
        Request $request,
        Services $services,
        EntityManagerInterface $manager, 
        TresorerieDepenseRepository $repoDepense
    ): Response {
        $data_session = $session->get('hotel');
        $depenses = $repoDepense->findAll();
        return $this->render('tresorerie/tresorerie_depense.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "depenses"          => $depenses
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
        // formulaire 
        
        return $this->render('tresorerie/formulaire_tres.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "type"              => $type
        ]);
    }

    /**
     * @Route("/profile/add_tres", name = "add_tres")
     */
    public function add_tres(Request $request, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $choix = $request->get('choix');
            $date = $request->get('date');
            $num_compte = $request->get('num_compte');
            $fournisseur = $request->get('fournisseur');
            $sage = $request->get('sage');
            $mode_pmt = $request->get('mode_pmt');
            $compte_b = $request->get('compte_b');
            $paiement = $request->get('paiement');
            $paiement = str_replace(" ", "", $paiement);
            $id_pro = $request->get('id_pro');
            $client = $request->get('client');
            $designation = $request->get('designation');
            $monnaie = $request->get('monnaie');
            
            if($choix == "depense"){
                $depense = new TresorerieDepense();
                $depense->setNumCompte($num_compte);
                $depense->setNomFournisseur($fournisseur);
                $depense->setDate(date_create($date));
                $depense->setDesignation($designation);
                $depense->setNumSage($sage);
                $depense->setModePaiement($mode_pmt);
                $depense->setCompteBancaire($compte_b);
                $depense->setPaiement($paiement);
                $depense->setMonnaie($monnaie);
                $manager->persist($depense);
                //dd($depense);
            }
            if ($choix == "recette") {
                $recette = new TresorerieRecette();
                $recette->setIdPro($id_pro);
                $recette->setNomClient($client);
                $recette->setDate(date_create($date));
                $recette->setDesignation($designation);
                $recette->setNumSage($sage);
                $recette->setModePaiement($mode_pmt);
                $recette->setCompteBancaire($compte_b);
                $recette->setMonnaie($monnaie);
                $recette->setPaiement($paiement);
                $manager->persist($recette);
                //dd($recette);
            }
            $manager->flush();
            $data = "ok";
            $data = json_encode($data);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/profile/lister_data_recette", name ="lister_data_recette")
     */
    public function lister_data_recette(Request $request, 
        TresorerieRecetteRepository $repoRecette
    ){
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');   
            $html  = '';
            $date1_o = null;
            $date2_o = null;
            if($date1 != "" && $date2 != ""){
                $date1_o = date_create($date1);
                $date2_o = date_create($date2);
            }
            $recettes = $repoRecette->findRecettebetween($date1_o, $date2_o);
            $t_recettes = count($recettes);
            if($t_recettes > 11){
                $html .= '
                <div class="dr_tab_trans">
                    <div class="block_all_tr block_tr_scroll">
                ';
            }
            else if($t_recettes <= 11){
                $html .= '
                <div class="dr_tab_trans">
                    <div class="block_all_tr">
                ';
            }

            foreach($recettes as $recette){
                $html .='
                <div class="t_body_row sous_tab_body t_body_tres">
                    <div class="tres_date">
                        <span>' . $recette->getDate()->format("d-m-Y") .'</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $recette->getDesignation() .'</span>
                    </div>
                    <div class="tres_idPro">
                        <span> '. $recette->getIdPro() .'</span>
                    </div>
                    <div class="tres_sage">
                        <span> '. $recette->getNumSage() . '</span>
                    </div>
                    <div class=" tres_client">

                        <span> ' . $recette->getNomClient() .'</span>
                    </div>
                    <div class="tres_mode_p">
                        <span> ' . $recette->getModePaiement() .'</span>
                    </div>
                    <div class="tres_compte_b">
                        <span>' . $recette->getCompteBancaire() .'</span>
                    </div>
                    <div class="tres_monnaie">
                        <span> ' . $recette->getMonnaie() .'</span>
                    </div>
                    <div class="tres_paiement sans_border">
                        <span class="montant">' . $recette->getPaiement() . '</span>
                    </div>

                </div>               
                ';
            }

            $html .= '
                    </div>
                </div>
            ';

            $data = $html;
            $data = json_encode($data);
            $response->headers->set('content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/profile/lister_data_depense", name ="lister_data_depense")
     */
    public function lister_data_depense(Request $request, 
        TresorerieDepenseRepository $repoDepense
    ){
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');   
            $html  = '';
            $date1_o = null;
            $date2_o = null;
            if($date1 != "" && $date2 != ""){
                $date1_o = date_create($date1);
                $date2_o = date_create($date2);
            }
            $recettes = $repoDepense->findRecettebetween($date1_o, $date2_o);
            $t_recettes = count($recettes);
            if($t_recettes > 11){
                $html .= '
                <div class="dr_tab_trans">
                    <div class="block_all_tr block_tr_scroll">
                ';
            }
            else if($t_recettes <= 11){
                $html .= '
                <div class="dr_tab_trans">
                    <div class="block_all_tr">
                ';
            }

            foreach($recettes as $recette){
                $html .='
                <div class="t_body_row sous_tab_body t_body_tres">
                    <div class="tres_date">
                        <span>' . $recette->getDate()->format("d-m-Y") .'</span>
                    </div>
                    <div class="tres_des">
                        <span>'. $recette->getDesignation() .'</span>
                    </div>
                    <div class="tres_idPro">
                        <span> '. $recette->getNumCompte() .'</span>
                    </div>
                    <div class="tres_sage">
                        <span> '. $recette->getNumSage() . '</span>
                    </div>
                    <div class=" tres_client">

                        <span> ' . $recette->getNomFournisseur() .'</span>
                    </div>
                    <div class="tres_mode_p">
                        <span> ' . $recette->getModePaiement() .'</span>
                    </div>
                    <div class="tres_compte_b">
                        <span>' . $recette->getCompteBancaire() .'</span>
                    </div>
                    <div class="tres_monnaie">
                        <span> ' . $recette->getMonnaie() .'</span>
                    </div>
                    <div class="tres_paiement sans_border">
                        <span class="montant">' . $recette->getPaiement() . '</span>
                    </div>

                </div>               
                ';
            }

            $html .= '
                    </div>
                </div>
            ';

            $data = $html;
            $data = json_encode($data);
            $response->headers->set('content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }
}
