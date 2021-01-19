<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\EntrepriseTW;
use App\Entity\DataTropicalWood;
use App\Form\FournisseurFileType;
use App\Entity\ContactEntrepriseTW;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EntrepriseTWRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DataTropicalWoodRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContactEntrepriseTWRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TropController extends AbstractController
{

    /**
     * @Route("/tropical_wood/home_tropical_wood", name="tropical_wood")
    */
    public function tropical_wood(SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
    {
        // $test = $repoTrop->findAllGroupedByEntreprise();
        // dd($test);
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "tropical_wood";
        $form_add = $this->createForm(FournisseurFileType::class);
        $form_add->handleRequest($request);
        $text = "tsisy";
        $all_entreprises = $repoEntre->findAllNomEntreprise();
        if ($form_add->isSubmitted() && $form_add->isValid()) {

            $fichier = $form_add->get('fichier')->getData();
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            /* $sheetname = "FOURNISSEURS";
                $reader->setLoadSheetsOnly($sheetname); */
            $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
            $data = $spreadsheet->getActiveSheet()->toArray();
            $d_aff = [];
            for ($i = 1; $i < count($data); $i++) {
                array_push($d_aff, $data[$i]);
            }
            //dd($d_aff);
            // on listes selectiopnne toutes les entreprise dans la base entrepriseTW

            // $All_entreprises = $this->getDoctrine()->getRepository(EntrepriseTW::class)->findAllNomEntreprise();

            for ($i = 0; $i < count($d_aff); $i++) {
                $data_tw = new DataTropicalWood();
                $idPro = $d_aff[$i][0];
                $entreprise = $d_aff[$i][2];
                // averina any @ liste_entreprise_client fa tsy mbola niainga ty methode ty taorian'ny import fichier excel
                if(!in_array($entreprise, $all_entreprises)){
                    //on insert l'entreprise dans la base de donnée table entrepriseTW
                    $entreprise_tw = new EntrepriseTW();
                    $entreprise_tw->setNom($entreprise);
                    $manager->persist($entreprise_tw);
                }
                $type_transaction = $d_aff[$i][1];
                $detail = $d_aff[$i][3];
                $etat_production = $d_aff[$i][5];
                $montant_total = $services->no_space(str_replace(",", " ", $d_aff[$i][10]));
                $montant_paye =  $services->no_space(str_replace(",", " ", $d_aff[$i][7])); // reste
                $montant_avance = $services->no_space(str_replace(",", " ", $d_aff[$i][9]));
                $date_confirmation = null;
                if ($services->parseMyDate($d_aff[$i][4]) != null) {
                    $date_confirmation = date_create($services->parseMyDate($d_aff[$i][4]));
                    if ($date_confirmation == false) {
                        return $this->render('page/tropical_wood.html.twig', [
                            "hotel"             => $data_session['pseudo_hotel'],
                            "current_page"      => $data_session['current_page'],
                            "form_add"          => $form_add->createView(),
                            'error'             => $i,
                            'tri'               => false,
                            'tropical_wood'     => true,
                            'datas'             => null,
                        ]);
                    }
                }
                // préparation de l'objet
                // on teste l'unicité de idPro
                $dataTrop = $repoTrop->findBy(['id_pro' => $idPro]);
                if(count($dataTrop)>0){
                    $data_tw->setTypeTransaction($type_transaction);
                    $data_tw->setEntreprise($entreprise);
                    $data_tw->setDetail($detail);
                    $data_tw->setEtatProduction($etat_production);
                    $data_tw->setMontantTotal($montant_total);
                    $data_tw->setMontantPaye($montant_paye);
                    $data_tw->setTotalReglement($montant_avance);
                    $data_tw->setDateConfirmation($date_confirmation);
                    $manager->persist($data_tw);
                }else{
                    $data_tw->setIdPro($idPro);
                    $data_tw->setTypeTransaction($type_transaction);
                    $data_tw->setEntreprise($entreprise);
                    $data_tw->setDetail($detail);
                    $data_tw->setEtatProduction($etat_production);
                    $data_tw->setMontantTotal($montant_total);
                    $data_tw->setMontantPaye($montant_paye);
                    $data_tw->setTotalReglement($montant_avance);
                    $data_tw->setDateConfirmation($date_confirmation);
                    $manager->persist($data_tw);
                }
                
            }
            
            $manager->flush();
            // on liste tous les data    
        }
        $datas = $repoTrop->findAll();
        $datasAsc = $repoTrop->findAllGroupedAsc();
        $Liste = [];
        //dd($datasAsc);
        foreach ($datasAsc as $d) {
            $tab_temp = [];
            $son_entreprise = $d[0]->getEntreprise();
            $liste = $repoTrop->findBy(["entreprise" => $son_entreprise]);

            $tab_temp["entreprise"] = $son_entreprise;
            $tab_temp["listes"] = $liste;
            $tab_temp["sous_total_montant_total"] = $d["sous_total_montant_total"];
            $tab_temp["sous_total_total_reglement"] = $d["sous_total_total_reglement"];
            $tab_temp["total_reste"] = $d["total_reste"];
            array_push($Liste, $tab_temp);
        }
        // dd($Liste);

        if ($request->request->count()) {
            $type_transaction = $request->request->get('type_transaction');
            $type_transaction = explode("*", $type_transaction);
            $etat_production = $request->request->get('etat_production');
            $etat_production = explode("*", $etat_production);
            $etat_paiement = $request->request->get('etat_paiement');
            $etat_paiement = explode("*", $etat_paiement);
            $Liste = $repoTrop->filtrer(
                $request->request->get('date1'), 
                $request->request->get('date2'), 
                $type_transaction, $etat_production, $etat_paiement, 
                null, null, "DESC"
            ); // typeReglement, typeReste, typeMontant
            //dd($les_datas);
            return $this->render('page/tropical_wood.html.twig', [
                "hotel"                     => $data_session['pseudo_hotel'],
                "current_page"              => $data_session['current_page'],
                "form_add"                  => $form_add->createView(),
                'datas'                     => $Liste,
                'tri'                       => false,
                'date1'                     => $request->request->get('date1'),
                'date2'                     => $request->request->get('date2'),
                'type_transaction'          => $type_transaction,
                'type_transaction_text'     => $request->request->get('type_transaction'),
                'etat_production'           => $etat_production,
                'etat_production_text'      => $request->request->get('etat_production'),
                'etat_paiement'             => $etat_paiement,
                'etat_paiement_text'        => $request->request->get('etat_paiement'),
                'tropical_wood'             => true,
                "id_page"                   => "li__dashboard_tw",
            ]);
        }
        return $this->render('page/tropical_wood.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            "form_add"          => $form_add->createView(),
            'datas'             => $Liste,
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"                   => "li__dashboard_tw",
        ]);
    }
    
    /**
     * @Route("/tropical_wood/tri_ajax_btn_black/tropical", name = "tri_ajax_btn_black")
     */
    public function tri_ajax_btn_black(Request $request, DataTropicalWoodRepository $repoTrop)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $typeReglement = $request->get('typeReglement');
            $typeReste = $request->get('typeReste');
            $typeMontant = $request->get('typeMontant'); 
            
            $type_transaction = $request->get('type_transaction');
            $type_transaction = explode("*", $type_transaction);
            $etat_production = $request->get('etat_production');
            $etat_production = explode("*", $etat_production);
            $etat_paiement = $request->get('etat_paiement');
            $etat_paiement = explode("*", $etat_paiement);
            if ($typeReglement == null && $typeReste == null && $typeMontant == null) {
                $typeMontant = "DESC";
            }
            $Liste = $repoTrop->filtrer(
                $request->request->get('date1'),
                $request->request->get('date2'),
                $type_transaction,
                $etat_production,
                $etat_paiement,
                $typeReglement,
                $typeReste,
                $typeMontant
            );
            

            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach($Liste as $data){
                    $stringP .= '

                <div>
                    <div class="t_body_row">
                        <div class="td_long" colspan="9">
                            <span>'. $data["entreprise"] . '</span>
                        </div>
                        <button clicked="false" class="btn_drop_data btn btn-warning btn-xs"><span class="fa fa-angle-up"></span></button>
                    </div>
                    ';
                        $string1 = '';
                        $total_reste = 0;
                        $total_reglement = 0;
                        $total_montant = 0;
                        foreach($data['listes'] as $item){
                            $reste = $item->getMontantTotal() - $item->getTotalReglement();
                            $total_reglement = $total_reglement + $item->getTotalReglement();
                            $total_montant = $total_montant + $item->getMontantTotal();
                            $total_reste += $reste;
                            $date = "";
                            if($item->getDateConfirmation() != null ){
                                $date = $item->getDateConfirmation()->format("d-m-Y");
                            }
                            $string1 .= '
                                <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;"
                                > 
                                    <div>
                                        <span>'. $item->getIdPro() . '</span>
                                    </div>
                                    <div>
                                        <span>'. $item->getDetail() .'</span>
                                    </div>
                                    <div>
                                        <span class="value">'. $item->getTypeTransaction() .'</span>
                                    </div>
                                    <div>
                                        <span class="value">'.$item->getEtatProduction(). '</span>
                                    </div>
                                    <div>
                                        <span class="montant value">' . $item->getTotalReglement() . '</span>
                                    </div>
                                    <div>
                                        <span class="montant">
                                            '. $reste .'
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant">'. $item->getMontantTotal() .'</span>
                                    </div>
                                    <div>
                                        <span>
                                        '. $date .'
                                        </span>
                                    </div>
                                </div>
                            ';
                        }
                    
                        $stringP .= $string1 ;
                    
                    $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div>
                                        <span>Sous-total</span> 
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span class="montant value total_paiement">
                                            '. $total_reglement .'
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div>
                                   
                                        <span class="montant value total_montant">'. $total_montant .'</span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                </div>
                        </div>
                    ';   
                    
                    $Total_Reglement = $Total_Reglement + $total_reglement;
                    $Total_Reste = $Total_Reste + $total_reste;
                    $Total_Montant = $Total_Montant + $total_montant;
            }

            $stringP .= '
                        <div class="t_footer_row sous_tab_body tota_content">
                            <div>
                                <span>Total</span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                        </div>
                    
                    ';

            
            
               
            $data = json_encode($stringP);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    

    /**
     * @Route("/tropical_wood/search_ajax_btn_ok/tropical", name = "search_ajax_btn_ok")
     */
    public function search_ajax_btn_ok(Request $request, DataTropicalWoodRepository $repoTrop)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {
            
            $input__entreprise_ajax = $request->get('input__entreprise_ajax');
            $tri_reglement = $request->get('tri_reglement');
            $tri_reste = $request->get('tri_reste');
            $tri_montant = $request->get('tri_montant');
           
            $input__entreprise_ajax = explode("*", $input__entreprise_ajax);

            $Liste = $repoTrop->searchEntrepriseContact($input__entreprise_ajax, $tri_reglement, $tri_reste, $tri_montant);

            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach ($Liste as $data) {
                $stringP .= '

                <div>
                    <div class="t_body_row">
                        <div class="td_long" colspan="9">
                            <span>' . $data["entreprise"] . '</span>
                        </div>
                        <button clicked="false" class="btn_drop_data btn btn-warning btn-xs"><span class="fa fa-angle-up"></span></button>
                    </div>';
                $string1 = '';
                $total_reste = 0;
                $total_reglement = 0;
                $total_montant = 0;
                foreach ($data['listes'] as $item) {
                    $reste = $item->getMontantTotal() - $item->getTotalReglement();
                    $total_reglement = $total_reglement + $item->getTotalReglement();
                    $total_montant = $total_montant + $item->getMontantTotal();
                    $total_reste += $reste;
                    $date = "";
                    if ($item->getDateConfirmation() != null) {
                        $date = $item->getDateConfirmation()->format("d-m-Y");
                    }
                    $string1 .= '
    
                            <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;">
                                <div>
                                    <span>' . $item->getIdPro() . '</span>
                                </div>
                                <div>
                                    <span>' . $item->getDetail() . '</span>
                                </div>
                                <div>
                                    <span class="value">' . $item->getTypeTransaction() . '</span>
                                </div>
                                <div>
                                    <span class="value">' . $item->getEtatProduction() . '</span>
                                </div>
                                <div>
                                    <span class="montant value">' . $item->getTotalReglement() . '</span>
                                </div>
                                <div>
                                    <span class="montant">
                                        ' . $reste . '
                                    </span>
                                </div>
                                <div>
                                    <span class="montant">' . $item->getMontantTotal() . '</span>
                                </div>
                                <div>
                                    <span>
                                       ' . $date . '
                                    </span>
                                </div>
                            </div>
                        ';
                }
                $stringP .= $string1;

                $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div>
                                        <span>Sous-total</span> 
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                    <div>
                                        <span class="montant value total_paiement">
                                            ' . $total_reglement . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div>
                                   
                                        <span class="montant value total_montant">' . $total_montant . '</span>
                                    </div>
                                    <div>
                                        <span></span>
                                    </div>
                                </div>
                        </div>
                    ';

                $Total_Reglement = $Total_Reglement + $total_reglement;
                $Total_Reste = $Total_Reste + $total_reste;
                $Total_Montant = $Total_Montant + $total_montant;
            }

            $stringP .= '
                        <div class="t_footer_row sous_tab_body tota_content">
                            <div>
                                <span>Total</span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                            <div>
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                            <div>
                                <span></span>
                            </div>
                        </div>
                    ';
            $data = json_encode($stringP);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/admin/tri/date_confirmation", name="tri.date_confirmation")
     */
    public function tri_date_confirmation(SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {
        $data_session = $session->get('hotel');
        $form_add = $this->createForm(FournisseurFileType::class);
        $form_add->handleRequest($request);
        $les_datas = [];
        $table = [];
        if($request->request->count() > 0){
            if(($request->request->get('date1') != "") && $request->request->get('date2') != ""){
                $date1 = date_create($request->request->get('date1'));
                $date2 = date_create($request->request->get('date2'));
                $all = $repoTrop->findAll();
                foreach ($all as $item) {
                    $date = $item->getDateConfirmation();
                    if (($date >= $date1) && ($date <= $date2)) {
                        array_push($table, $item);
                        array_push($les_datas, $table);
                    }
                }
                return $this->render('page/tropical_wood.html.twig', [
                    "hotel"             => $data_session['pseudo_hotel'],
                    "current_page"      => $data_session['current_page'],
                    "form_add"          => $form_add->createView(),
                    'datas'             => $les_datas,
                    'tri'               => true,
                    'date1'             => $request->request->get('date1'),
                    'date2'             => $request->request->get('date2'),
                    'tropical_wood'             => true,
                    "id_page"                   => "li__dashboard_tw",
                ]);
            }
            else{
                return $this->redirectToRoute("tropical_wood");
            }
        }
       
    }

    /**
     * @Route("/tropical_wood/search_ajax_ec", name = "search_ajax_ec")
     */
    public function search_ajax_ec(Request $request, DataTropicalWoodRepository $repoTrop, EntityManagerInterface $em)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $mot = $request->get('mot');

            $RAW_QUERY = "SELECT DISTINCT  entreprise FROM data_tropical_wood WHERE data_tropical_wood.entreprise LIKE '%".$mot."%' LIMIT 10;";

            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();

            $result = $statement->fetchAll();
            
            $data = json_encode($result);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /*entreprise/contact*/

    /**
     * @Route("/tropical_wood/liste_client_tw", name="liste_client_tw")
     */
    public function liste_client_tw(SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "tropical_wood";
        $datas = $repoTrop->findAll();
        $datasAsc = $repoTrop->findAllGroupedAsc();
        $Liste = [];
        //dd($datasAsc);
        
        foreach ($datasAsc as $d) {
            $tab_temp = [];
            $son_entreprise = $d[0]->getEntreprise();
            $liste = $repoTrop->findBy(["entreprise" => $son_entreprise]);

            $tab_temp["entreprise"] = $son_entreprise;
            $tab_temp["listes"] = $liste;
            $tab_temp["sous_total_montant_total"] = $d["sous_total_montant_total"];
            $tab_temp["sous_total_total_reglement"] = $d["sous_total_total_reglement"];
            $tab_temp["total_reste"] = $d["total_reste"];
            array_push($Liste, $tab_temp);
        }
        return $this->render('page/liste_client_tw.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'datas'             => $Liste,
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"                   => "li_entreprise_contact",
        ]);
    }
    /**
     * @Route("/tropical_wood/show_entreprise/{nom_entreprise}", name="show_entreprise")
     */
    public function show_entreprise($nom_entreprise, SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {   
        //dd($nom_entreprise);
        $data_session = $session->get('hotel');
        $data_session['pseudo_hotel'] = "tropical_wood";

        $All_data_ne = $repoTrop->findBy([
            'entreprise'    => $nom_entreprise,
        ]);
        // Calcul des chiffres d'affaire

        $ca = 0;
        $cae = 0;
        $caae = 0;

        foreach($All_data_ne as $item){
            $ca = $ca + $item->getMontantTotal();
            $cae = $cae + $item->getTotalReglement();
            $r = $item->getMontantTotal() - $item->getTotalReglement();
            $caae = $caae + $r;
        }

        // les donnée pour transaction en cours et celles qui sont terminées
        $tab_trans_enc = [];
        $tab_trans_ter = [];
        foreach ($All_data_ne as $item) {
            $ca = $ca + $item->getMontantTotal();
            $cae = $cae + $item->getTotalReglement();
            $r = $item->getMontantTotal() - $item->getTotalReglement();
           if($r > 0){
               array_push($tab_trans_enc, $item);
           }else{
                array_push($tab_trans_ter, $item);
           }
        }

        // liste des pf dans tab_trans_enc
        $liste_pf = [];

        foreach($tab_trans_enc as $item){
            array_push($liste_pf, $item->getIdPro());
        }
        

        //dd($All_data_ne);
        return $this->render('page/show_entreprise.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_entreprise_contact",
            "datas"             => $All_data_ne,
            "nom_client"        => $nom_entreprise,
            "chiffre_daf"       => $ca,
            "chiffre_daf_enc"   => $cae,
            "chiffre_daf_a_enc" => $caae,
            "tab_trans_enc"     => $tab_trans_enc,
            "tab_trans_ter"     => $tab_trans_ter,
            "liste_pf"          => $liste_pf,
        ]);
    }

    /**
     * @Route("/tropical_wood/entreprise/addAdresse", name = "add_address")
     */
    public function add_address(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $adresse = $request->get('adresse');
            $entreprise = $request->get('entreprise');

            $entreprise = $repoEntre->findBy(["nom"=>$entreprise]);
            $entreprise[0]->setAdresse($adresse);
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Ministère de la communication et de la culture"]);
            $entreprise[0]->setAdresse("tsoka");
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/add_contact", name = "add_contact")
     */
    public function add_contact(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $entreprise = $request->get('entreprise');
            $nom_en_contact = $request->get('nom_en_contact');
            $type = $request->get('type');
            $email = $request->get('email');
            $telephone = $request->get('telephone');

            $ce = new ContactEntrepriseTW();
            $ce->setNomEnContact($nom_en_contact);
            $ce->setType($type);
            $ce->setEmail($email);
            $ce->setTelephone($telephone);
            $entreprise = $repoEntre->findBy(["nom"=>$entreprise]);
            //$entreprise[0]->setAdresse($adresse);
            $ce->setEntreprise($entreprise[0]);
            $manager->persist($ce);
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Ministère de la communication et de la culture"]);
            $entreprise[0]->setAdresse("tsoka");
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/entreprise/listercontact", name = "listercontact")
     */
    public function listercontact(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager, ContactEntrepriseTWRepository $repoContact)
    {
        $response = new Response();
        
        if($request->isXmlHttpRequest()){

            $entreprise = $request->get('entreprise');           
            $entreprise = $repoEntre->findBy(["nom"=>$entreprise]);
            $contacts = $entreprise[0]->getContactEntrepriseTWs();

            $html = '

                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
               

            foreach ($contacts as $item) {
               $html .=
                    '
                    <tr>
                        <td><span>'. $item->getNomEnContact() . '</span></td>
                        <td><span>' . $item->getType() . '</span></td>
                        <td><span>' . $item->getEmail() . '</span></td>
                        <td><span>' . $item->getTelephone() . '</span></td>
                        <td>
                            <div class="list_action">
                                <a href="#">
                                    <span class="fa fa-edit"></span>
                                </a>
                                <a href="#">
                                    <span class="fa fa-trash-o"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                
                ';
            }
            
            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Tany be club"]);
            $contacts = $entreprise[0]->getContactEntrepriseTWs();
            foreach($contacts as $item){
                dd($item);
            }
        }
        return $response;
    }
    

    /**
     * @Route("/tropical_wood/entreprise/listerAdresse", name = "listerAdresse")
     */
    public function listerAdresse(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $entreprise = $request->get('entreprise');

            $entreprise = $repoEntre->findBy(["nom"=>$entreprise]);
            $html = '
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span>'. $entreprise[0]->getNom() . '</span></td>
                    <td><span>' . $entreprise[0]->getAdresse() . '</span></td>
                    <td>
                        <div class="list_action">
                            <a href="#" class="tab_adress_edit" data-id="'.  $entreprise[0]->getId() .'" 
                                data-adresse ="'. $entreprise[0]->getAdresse() .'"
                            >
                                <span class="fa fa-edit"></span>
                            </a>
                        </div>
                    </td>
                </tr>
            ' ;
            
            $manager->flush();
            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Ministère de la communication et de la culture"]);
            $entreprise[0]->setAdresse("tsoka");
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/entreprise/edit/addAdresse", name = "editAdresse")
     */
    public function editAdresse(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $entreprise = $request->get('entreprise');
            $adresse = $request->get('adresse');

            $entreprise = $repoEntre->findBy(["nom"=>$entreprise]);
            $entreprise[0]->setAdresse($adresse);
            
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Ministère de la communication et de la culture"]);
            $entreprise[0]->setAdresse("tsoka");
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/tri_ajax_entreprise_client", name = "tri_ajax_entreprise_client")
     */
    public function tri_ajax_entreprise_client(Request $request, DataTropicalWoodRepository $repoTrop)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $typeReglement = $request->get('typeReglement');
            $typeReste = $request->get('typeReste');
            $typeMontant = $request->get('typeMontant');

            $type_transaction = $request->get('type_transaction');
            $type_transaction = explode("*", $type_transaction);
            $etat_production = $request->get('etat_production');
            $etat_production = explode("*", $etat_production);
            $etat_paiement = $request->get('etat_paiement');
            $etat_paiement = explode("*", $etat_paiement);
            if ($typeReglement == null && $typeReste == null && $typeMontant == null) {
                $typeMontant = "DESC";
            }
            $Liste = $repoTrop->filtrer(
                $request->request->get('date1'),
                $request->request->get('date2'),
                $type_transaction,
                $etat_production,
                $etat_paiement,
                $typeReglement,
                $typeReste,
                $typeMontant
            );


            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach ($Liste as $data) {
                //dd($data['listes']);
                $stringP .= '

                <div>
                    ';
                $string1 = '';
                $total_reste = 0;
                $total_reglement = 0;
                $total_montant = 0;
                foreach ($data['listes'] as $item) {
                    $reste = $item->getMontantTotal() - $item->getTotalReglement();
                    $total_reglement = $total_reglement + $item->getTotalReglement();
                    $total_montant = $total_montant + $item->getMontantTotal();
                    $total_reste += $reste;
                    $date = "";
                    if ($item->getDateConfirmation() != null) {
                        $date = $item->getDateConfirmation()->format("d-m-Y");
                    }
                    $string1 .= '
                                <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;"
                                > 
                                    <div>
                                        <span class="montant value">' . $item->getTotalReglement() . '</span>
                                    </div>
                                    <div>
                                        <span class="montant">
                                            ' . $reste . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant">' . $item->getMontantTotal() . '</span>
                                    </div>
                                </div>
                            ';
                }

                $stringP .= $string1;

                $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div>
                                        <a href="/tropical_wood/show_entreprise/' . $data["entreprise"] . '" class="link_client">
                                            <span>' . $data["entreprise"] . '</span>
                                        </a>
                                    </div>
                                    <div>
                                        <span class="montant value total_paiement">
                                            ' . $total_reglement . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div>
                                        <span class="montant value total_montant">' . $total_montant . '</span>
                                    </div>
                                </div>
                        </div>
                    ';

                $Total_Reglement = $Total_Reglement + $total_reglement;
                $Total_Reste = $Total_Reste + $total_reste;
                $Total_Montant = $Total_Montant + $total_montant;
            }

            $stringP .= '
                        <div class="t_footer_row sous_tab_body tota_content">
                            <div>
                                <span>Total</span>
                            </div>
                            <div>
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                        </div>
                    
                    ';




            $data = json_encode($stringP);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/tri_ajax_btn_black/tropical_entreprise_client", name = "tri_ajax_btn_black_tropical_entreprise_client")
     */
    public function tri_ajax_btn_black_tropical_entreprise_client(Request $request, DataTropicalWoodRepository $repoTrop)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $typeReglement = $request->get('typeReglement');
            $typeReste = $request->get('typeReste');
            $typeMontant = $request->get('typeMontant');

            $type_transaction = $request->get('type_transaction');
            $type_transaction = explode("*", $type_transaction);
            $etat_production = $request->get('etat_production');
            $etat_production = explode("*", $etat_production);
            $etat_paiement = $request->get('etat_paiement');
            $etat_paiement = explode("*", $etat_paiement);
            if ($typeReglement == null && $typeReste == null && $typeMontant == null) {
                $typeMontant = "DESC";
            }
            $Liste = $repoTrop->filtrer(
                $request->request->get('date1'),
                $request->request->get('date2'),
                $type_transaction,
                $etat_production,
                $etat_paiement,
                $typeReglement,
                $typeReste,
                $typeMontant
            );


            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach ($Liste as $data) {
                $stringP .= '

                <div>
                    ';
                $string1 = '';
                $total_reste = 0;
                $total_reglement = 0;
                $total_montant = 0;
                foreach ($data['listes'] as $item) {
                    $reste = $item->getMontantTotal() - $item->getTotalReglement();
                    $total_reglement = $total_reglement + $item->getTotalReglement();
                    $total_montant = $total_montant + $item->getMontantTotal();
                    $total_reste += $reste;
                    $date = "";
                    if ($item->getDateConfirmation() != null) {
                        $date = $item->getDateConfirmation()->format("d-m-Y");
                    }
                    $string1 .= '
                                <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;"
                                > 
                                    <div>
                                        <span class="montant value">' . $item->getTotalReglement() . '</span>
                                    </div>
                                    <div>
                                        <span class="montant">
                                            ' . $reste . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant">' . $item->getMontantTotal() . '</span>
                                    </div>
                                    
                                </div>
                            ';
                }

                $stringP .= $string1;

                $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div>
                                        <a href="#" class="link_client">
                                            <span>' . $data["entreprise"] . '</span>
                                        </a> 
                                    </div>
                                   
                                    <div>
                                        <span class="montant value total_paiement">
                                            ' . $total_reglement . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div>
                                   
                                        <span class="montant value total_montant">' . $total_montant . '</span>
                                    </div>
                                   
                                </div>
                        </div>
                    ';

                $Total_Reglement = $Total_Reglement + $total_reglement;
                $Total_Reste = $Total_Reste + $total_reste;
                $Total_Montant = $Total_Montant + $total_montant;
            }

            $stringP .= '
                        <div class="t_footer_row sous_tab_body tota_content">
                            <div>
                                <span>Total</span>
                            </div>
                            <div>
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                        </div>
                    
                    ';




            $data = json_encode($stringP);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/search_ajax_btn_ok/tropical_entreprise_client", name = "search_ajax_btn_ok_tropical_entreprise_client")
     */
    public function search_ajax_btn_ok_tropical_entreprise_client(Request $request, DataTropicalWoodRepository $repoTrop)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $input__entreprise_ajax = $request->get('input__entreprise_ajax');
            $tri_reglement = $request->get('tri_reglement');
            $tri_reste = $request->get('tri_reste');
            $tri_montant = $request->get('tri_montant');

            $input__entreprise_ajax = explode("*", $input__entreprise_ajax);

            $Liste = $repoTrop->searchEntrepriseContact($input__entreprise_ajax, $tri_reglement, $tri_reste, $tri_montant);

            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach ($Liste as $data) {
                $stringP .= '

                <div>
                    ';
                $string1 = '';
                $total_reste = 0;
                $total_reglement = 0;
                $total_montant = 0;
                foreach ($data['listes'] as $item) {
                    $reste = $item->getMontantTotal() - $item->getTotalReglement();
                    $total_reglement = $total_reglement + $item->getTotalReglement();
                    $total_montant = $total_montant + $item->getMontantTotal();
                    $total_reste += $reste;
                    $date = "";
                    if ($item->getDateConfirmation() != null) {
                        $date = $item->getDateConfirmation()->format("d-m-Y");
                    }
                    $string1 .= '
    
                            <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;">
                                
                                <div>
                                    <span class="montant value">' . $item->getTotalReglement() . '</span>
                                </div>
                                <div>
                                    <span class="montant">
                                        ' . $reste . '
                                    </span>
                                </div>
                                <div>
                                    <span class="montant">' . $item->getMontantTotal() . '</span>
                                </div>
                            </div>
                        ';
                }
                $stringP .= $string1;

                $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div>
                                        <a href="#" class="link_client">
                                            <span>' . $data["entreprise"] . '</span>
                                        </a>
                                    </div>
                                    <div>
                                        <span class="montant value total_paiement">
                                            ' . $total_reglement . '
                                        </span>
                                    </div>
                                    <div>
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div>
                                        <span class="montant value total_montant">' . $total_montant . '</span>
                                    </div>
                                </div>
                        </div>
                    ';

                $Total_Reglement = $Total_Reglement + $total_reglement;
                $Total_Reste = $Total_Reste + $total_reste;
                $Total_Montant = $Total_Montant + $total_montant;
            }

            $stringP .= '
                        <div class="t_footer_row sous_tab_body tota_content">
                            <div>
                                <span>Total</span>
                            </div>
                            <div>
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div>
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                        </div>
                    ';
            $data = json_encode($stringP);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
    

}
