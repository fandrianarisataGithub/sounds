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
    public function add_tres(Request $request, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
           /* "date"			: $("#date").val(),
            "designation"	: $("#designation").val(),
            "prestataire"	: $('#prestataire').val(),
            "sage"			: $('#sage').val(),
            "mode_pmt"		: $('#mode_pmt').val(),
            "compte_b"		: $("#compte_b").val(),
            "id_pro"		: $("#id_pro").val(),
            "client"		: $('#client').val(),
            "monnaie"		: $('#monnaie').val(),
            "encaissement"	: $("#encaissement_montant").val(),
            "decaissement"	: $("#decaissement_montant").val(),
            "categorie"		: $("#categorie").val(),
            "sous_categorie"	: $("#sous_categorie").val(),*/
            $date = date_create($request->get('date'));
            $designation = $request->get('designation');
            $mode_pmt = $request->get('mode_pmt');
            $compte = $request->get('compte');

           if($request->get('encaissement_montant')){

           }
           else if($request->get('encaissement_montant')){

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
    public function lister_data_recette(Request $request
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
           
            
           
            // foreach($recettes as $recette){
            //     $html .='
            //     <div class="t_body_row sous_tab_body t_body_tres">
            //         <div class="tres_date">
            //             <span>' . $recette->getDate()->format("d-m-Y") .'</span>
            //         </div>
            //         <div class="tres_des">
            //             <span>'. $recette->getDesignation() .'</span>
            //         </div>
            //         <div class="tres_idPro">
            //             <span> '. $recette->getIdPro() .'</span>
            //         </div>
            //         <div class="tres_sage">
            //             <span> '. $recette->getNumSage() . '</span>
            //         </div>
            //         <div class=" tres_client">

            //             <span> ' . $recette->getNomClient() .'</span>
            //         </div>
            //         <div class="tres_mode_p">
            //             <span> ' . $recette->getModePaiement() .'</span>
            //         </div>
            //         <div class="tres_compte_b">
            //             <span>' . $recette->getCompteBancaire() .'</span>
            //         </div>
            //         <div class="tres_monnaie">
            //             <span> ' . $recette->getMonnaie() .'</span>
            //         </div>
            //         <div class="tres_paiement sans_border">
            //             <span class="montant">' . $recette->getPaiement() . '</span>
            //         </div>

            //     </div>               
            //     ';
            // }

        //     $html .= '
        //             </div>
        //         </div>
        //     ';

        //     $data = $html;
        //     $data = json_encode($data);
        //     $response->headers->set('content-Type', 'application/json');
        //     $response->setContent($data);
        }
        return $response;
    }
}
