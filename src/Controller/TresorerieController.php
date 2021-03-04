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
        EntityManagerInterface $manager, TresorerieRecetteRepository $repoRecette
    ): Response
    {
        $data_session = $session->get('hotel');
        $recettes = $repoRecette->findAll();
        return $this->render('tresorerie/tresorerie_recette.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_tresoreriet",
            "recettes"          => $recettes,
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
}
