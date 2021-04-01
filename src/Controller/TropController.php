<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\ChangementPF;
use App\Entity\EntrepriseTW;
use App\Entity\ClientUpdated;
use App\Entity\ListePFUpdated;
use App\Entity\ListeChangement;
use App\Entity\DataTropicalWood;
use App\Entity\IntervalChangePF;
use App\Form\FournisseurFileType;
use App\Entity\ContactEntrepriseTW;
use App\Entity\RemarqueEntrepriseTW;
use App\Entity\ChangementAfterImport;
use App\Repository\ContactTwRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChangementPFRepository;
use App\Repository\EntrepriseTWRepository;
use Symfony\Contracts\Cache\ItemInterface;
use App\Repository\ClientUpdatedRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\ListePFUpdatedRepository;
use App\Repository\ListeChangementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DataTropicalWoodRepository;
use App\Repository\IntervalChangePFRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContactEntrepriseTWRepository;
use App\Repository\RemarqueEntrepriseTWRepository;
use App\Repository\ChangementAfterImportRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TropController extends AbstractController
{

    /**
     * @Route("/tropical_wood/home_tropical_wood", name="tropical_wood")
    */
    public function tropical_wood(CacheInterface $cache_init_tw, SessionInterface $session, 
                                Request $request, Services $services, EntityManagerInterface $manager, 
                                DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre,
                                IntervalChangePFRepository $repoInterval)
    {
        // $test = $repoTrop->findAllGroupedByEntreprise();
        // dd($test);
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "tropical_wood";
        $form_add = $this->createForm(FournisseurFileType::class);
        $form_add->handleRequest($request);
        $today = new \Datetime();
        $today = $today->format("Y-m-d");
        $today = date_create($today);
        $d = $repoTrop->findAll();
        $last_insert = $repoTrop->findLastinsert();
        $last_date_insertion = $last_insert[0]->getCreatedAt();
        if ($form_add->isSubmitted() && $form_add->isValid()) {
            // date de l'ancien chargement
            // on clean les mot de la base de donnée d'avant
            // on clean les mauvaise formation de nom dans entreprise
            $cache_init_tw->delete("init_tw");
            if(count($d)>0){
                foreach ($d as $item) {
                    $son_nom = $item->getEntreprise();
                    $son_id_pro = $item->getIdPro();
                    if (strpos($son_nom, "\n") !== false) {
                        $son_nom = str_replace("\n", " ", $son_nom);
                        $son_nom = trim(str_replace("  ", " ", $son_nom));
                        $item->setEntreprise(mb_strtoupper($son_nom, 'UTF-8'));
                        $manager->flush();
                    }
                    if (strpos($son_id_pro, "\n") !== false) {
                        $son_id_pro = str_replace("\n", " ", $son_id_pro);
                        $son_id_pro = trim(str_replace("  ", " ", $son_id_pro));
                        $item->setIdPro($son_id_pro);
                        $manager->flush();
                    }
                }
            }
            

            $fichier = $form_add->get('fichier')->getData();
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            // $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            // dd($safeFilename1);
            $newFilename1 = $originalFilename1 . '.' . $fichier->guessExtension();
            
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

           
            $Total_data_updated = [];
            for ($i = 0; $i < count($d_aff); $i++) {
                
                $data_tw = new DataTropicalWood();
                
                $idPro = $services->clean_word($d_aff[$i][0]);
                
                $entreprise = $services->clean_word($d_aff[$i][2]);

                $entreprise = mb_strtoupper($entreprise, 'UTF-8');
                
                $type_transaction = $services->clean_word($d_aff[$i][1]);
                $detail = $d_aff[$i][3];
               
                $etat_production = $d_aff[$i][6];
                // déjà le reste n'est plus à calculer:
                $montant_total = $services->no_space(str_replace(",", " ", $d_aff[$i][10]));
                $reste =  $services->no_space(str_replace(",", " ", $d_aff[$i][7])); // reste
                $montant_avance = $services->no_space(str_replace(",", " ", $d_aff[$i][9]));
                // on enlève les chiffre apres la virgule
                $t_mt = explode('.', $montant_total);
                $t_reste = explode('.', $reste);
                $t_ma = explode('.', $montant_avance);
                $montant_total = $t_mt[0];
                $reste = $t_reste[0];
                $montant_avance = $t_ma[0];
                $date_confirmation = null;
                $date_facture = null;
                $etape_production = $d_aff[$i][11];
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

                if ($services->parseMyDate($d_aff[$i][5]) != null) {
                    $date_facture = date_create($services->parseMyDate($d_aff[$i][5]));
                    if ($date_facture == false) {
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
                $dataTrop = $repoTrop->findOneByIdPro($idPro);
                
                
                if($dataTrop != null){
                    // s'il ya un ou des changements
                    $etape_production = floatVal(trim(str_replace("%", "", $etape_production)));
                    $son_type_trans = $dataTrop->getTypeTransaction() ;
                    $son_entreprise = $dataTrop->getEntreprise();
                    $son_detail = $dataTrop->getDetail();
                    $son_etat_prod =  $dataTrop->getEtatProduction();
                    $son_montant = $dataTrop->getMontantTotal();
                    $son_reste = $dataTrop->getReste();
                    $son_total_reglement =  $dataTrop->getTotalReglement();
                    $sa_date_conf =  $dataTrop->getDateConfirmation();
                    $sa_date_fact = $dataTrop->getDateFacture();
                    $son_etape_prod = $dataTrop->getEtapeProduction();
                    $synthese = [
                        "entreprise"        => $entreprise,
                        "id_pro"            => $dataTrop->getIdPro(),
                        "date_avant"        => $dataTrop->getCreatedAt(),
                        "date_MAJ"          => $today,
                        "liste_changement"  => []
                    ];
                    // ancien tab 
                    $ancien = [
                        "entreprise"        => $son_entreprise,
                        "type_transaction"  => $son_type_trans,
                        "details"           => $son_detail,
                        "etat_production"   => $son_etat_prod,
                        "montant_total"     => $son_montant,
                        "reste"             => $son_reste,
                        "total_reglement"   => $son_total_reglement,
                        "date_confirmation" => $sa_date_conf,
                        "date_facture"      => $sa_date_fact,
                        "etape_production"  => $son_etape_prod
                    ];
                    $nouveau = [
                        "entreprise"        => $entreprise,
                        "type_transaction"  => $type_transaction,
                        "details"           => $detail,
                        "etat_production"   => $etat_production,
                        "montant_total"     => $montant_total,
                        "reste"             => $reste,
                        "total_reglement"   => $montant_avance,
                        "date_confirmation" => $date_confirmation,
                        "date_facture"      => $date_facture,
                        "etape_production"  => $etape_production
                    ];
                    foreach($ancien as $key => $value){
                        $val_a_tester = ['total_reglement', 'reste', 'montant_total'];
                        if($ancien[$key] != $nouveau[$key]){
                           if(!in_array($key, $val_a_tester)){
                                $changement = [
                                    "nom_changement"    => $key,
                                    "last_data"         => $ancien[$key],
                                    "next_data"         => $nouveau[$key]
                                ];
                                array_push($synthese["liste_changement"], $changement);
                           }
                           else{
                               $val = abs($ancien[$key] - $nouveau[$key]);
                                if($val >= 1){
                                    
                                    $changement = [
                                        "nom_changement"    => $key,
                                        "last_data"         => $ancien[$key],
                                        "next_data"         => $nouveau[$key]
                                    ];
                                    array_push($synthese["liste_changement"], $changement);
                                }
                           }
                           
                        }
                    }
                    if(count($synthese["liste_changement"]) > 0){
                        array_push($Total_data_updated, $synthese);
                    }
                    // on met à jour
                   
                    $dataTrop->setIdPro($idPro);
                    $dataTrop->setCreatedAt($today);
                    $dataTrop->setTypeTransaction($type_transaction);
                    $dataTrop->setEntreprise($entreprise);
                    $dataTrop->setDetail($detail);
                    $dataTrop->setEtatProduction($etat_production);
                    $dataTrop->setMontantTotal($montant_total);
                    $dataTrop->setReste($reste);
                    $dataTrop->setDateFacture($date_facture);
                    $etape_production = floatVal(trim(str_replace("%",
                        "",
                        $etape_production
                    )));
                    $dataTrop->setEtapeProduction($etape_production);
                    $dataTrop->setTotalReglement($montant_avance);
                    $dataTrop->setDateConfirmation($date_confirmation);
                    
                }else if($dataTrop == null){
                    $data_new = new DataTropicalWood();
                    if($idPro != null && $entreprise != null){
                        $data_new->setIdPro($idPro);
                        $data_new->setCreatedAt($today);
                        $data_new->setTypeTransaction($type_transaction);
                        $data_new->setEntreprise($entreprise);
                        $data_new->setDetail($detail);
                        $data_new->setEtatProduction($etat_production);
                        $data_new->setMontantTotal(intval($montant_total));
                        $data_new->setReste(intval($reste));
                        $data_new->setDateFacture($date_facture);
                        $etape_production = floatVal(trim(str_replace("%", "", $etape_production)));
                        $data_new->setEtapeProduction($etape_production);
                        $data_new->setTotalReglement(intval($montant_avance));
                        $data_new->setDateConfirmation($date_confirmation);
                        $manager->persist($data_new);
                    }
                    
                }
                $manager->flush();
                
            }
            //dd($Total_data_updated);
            // insertion de l'interval de changement
            $date_last_text = $last_date_insertion->format('d-m-Y');
            $date_next_text = $today->format('d-m-Y');
            $intervalle_date = $date_last_text . " - " . $date_next_text;
            $l_interval = $repoInterval->findOneByIntervalle($intervalle_date);
            $interval = new IntervalChangePF();
            if(!$l_interval){
                
                $interval->setDateLast($last_date_insertion);
                $interval->setDateNext($today);
                $interval->setIntervalle($intervalle_date);
                $repoInterval = $this->getDoctrine()->getRepository(IntervalChangePF::class);
                $test_interval = $repoInterval->findBy(['intervalle' => $intervalle_date]);
                // dd($intervalle_date);
                if (count($test_interval) > 0) {
                    $interval = $test_interval[0];
                } else if (count($test_interval) == 0){
                    $manager->persist($interval);
                }
            }  
            // les entreprises 
           //dd($Total_data_updated);
            foreach($Total_data_updated as $Item){
                $nom_entreprise = $Item['entreprise'];
                // s'il y a déjà un nom d'entreprise identique dans cet interval
                
                $le_client = new ClientUpdated();
                $le_client->setNom($nom_entreprise);
                if($l_interval){
                    $le_client->addIntervalchangePF($l_interval);
                }
                else if($l_interval == null){
                    $le_client->addIntervalchangePF($interval);
                }
                // insertion de ListePFUpdated tokony- unitePFUpdated
                $unite_pf = new ListePFUpdated();
                $unite_pf->setIdPro($Item['id_pro']);
                $unite_pf->setClientUpdated($le_client);
                
                // insertion des changement
                foreach($Item['liste_changement'] as $item_change){
                    $changement = new ChangementAfterImport();
                    $changement->setNom($item_change['nom_changement']);
                    $type_data = gettype($item_change['last_data']);
                    if($type_data != "object"){
                        $changement->setLastData($item_change['last_data']);
                        if(gettype($item_change['next_data']) == "object"){
                            $data_string3 = $item_change['next_data']->format('d-m-Y');
                            $changement->setNextData($data_string3);
                        }
                        else{
                            $changement->setNextData($item_change['next_data']);
                        }
                    }else{
                        if($item_change['last_data'] != null){
                            $data_string1 = $item_change['last_data']->format('d-m-Y');
                        }
                        else if ($item_change['last_data'] == null) {
                            $data_string1 = "";
                        }
                        if ($item_change['next_data'] != null) {
                            $data_string2 = $item_change['next_data']->format('d-m-Y');
                        }
                        else if ($item_change['next_data'] == null) {
                            $data_string2 = "";
                        }
                        
                        $changement->setLastData($data_string1);
                        $changement->setNextData($data_string2);
                    }
                    $changement->setListePFUpdated($unite_pf);
                    $manager->persist($changement);
                }
                $manager->persist($le_client);
                $manager->persist($unite_pf);
                
            }
           
            $manager->flush();
        }
        
        // on crée les entreprise

        $all = $repoTrop->findAll();
        $i = 0;
        foreach($all as $item){
            
            $tab_nom_entre = $repoEntre->findAllNomEntreprise();
            $nom = $item->getEntreprise();
            
           if($nom){
                if (!in_array($nom, $tab_nom_entre)) {
                    $entreprise_objet = new EntrepriseTW();
                    $entreprise_objet->setNom($nom);
                    $manager->persist($entreprise_objet);
                    $manager->flush();
                }
           }
        }
        $Liste = $cache_init_tw->get('init_tw', function () use ($repoTrop) {
            return $repoTrop->filtrer(
                "",
                "",
                "",
                "",
                [0 => ""],
                [0 => ""],
                [0 => ""],
                null,
                null,
                "DESC"
            );
        });
        
        return $this->render('page/tropical_wood.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            "form_add"          => $form_add->createView(),
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"                   => "li__dashboard_tw",
        ]);
    }

    /**
     * @Route("/tropical_wood/delete_pf", name = "delete_pf")
     */
    public function delete_pf(CacheInterface $cache_liste_tw, Request $request, DataTropicalWoodRepository $repoTrop, EntityManagerInterface $manager)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id = $request->get('id');
            $data = $repoTrop->find($id);
            $manager->remove($data);
            $manager->flush();
            $test = $repoTrop->find($id);
            $data = "Impossible de supprimer";
            if($test == null){
                $data = json_encode("suppression ok");
            }
            // on delete le cache 
            $cache_liste_tw->delete("init_tw");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }
    
    /**
     * @Route("/tropical_wood/tri_ajax_btn_black/tropical", name = "tri_ajax_btn_black")
     */
    public function tri_ajax_btn_black(CacheInterface $cache_liste_tw, Request $request, DataTropicalWoodRepository $repoTrop, SessionInterface $session, EntrepriseTWRepository $repoEntre)
    {
        $response = new Response();
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $user = $data_session['user'];
        $profile_user = $user->getProfile();
        if($request->isXmlHttpRequest()){
            
            $typeReglement = $request->get('typeReglement');
            $filtre = $request->get('filtre');
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
            
            $Liste = [];
            if($filtre == "filtrer"){
                    $Liste = $repoTrop->filtrer(
                        $request->request->get('date1'),
                        $request->request->get('date2'),
                        $request->request->get('date3'),
                        $request->request->get('date4'),
                        $type_transaction,
                        $etat_production,
                        $etat_paiement,
                        $typeReglement,
                        $typeReste,
                        $typeMontant
                    );
            }
            if ($filtre == "init") {
                $Liste = $cache_liste_tw->get('init_tw', function () use ($repoTrop){
                    return $repoTrop->filtrer(
                        "",
                        "",
                        "",
                        "",
                        [0=>""],
                        [0=>""],
                        [0=>""],
                        null,
                        null,
                        "DESC"
                    );

                });

                // $cache = new FilesystemAdapter();
                // $Liste = $cache->get('my_cache_key', function (ItemInterface $item) use ($repoTrop) {
                //     $item->expiresAfter(3600);
                //     //$repoTrop = $this->getDoctrine()->getRepository(DataTropicalWood::class);
                //     return $repoTrop->filtrer(
                //         "",
                //         "",
                //         "",
                //         "",
                //         [0 => ""],
                //         [0 => ""],
                //         [0 => ""],
                //         null,
                //         null,
                //         "DESC"
                //     );
                // });
               
            }

            // $Liste = $repoTrop->filtrer(
            //     $request->request->get('date1'),
            //     $request->request->get('date2'),
            //     $request->request->get('date3'),
            //     $request->request->get('date4'),
            //     $type_transaction,
            //     $etat_production,
            //     $etat_paiement,
            //     $typeReglement,
            //     $typeReste,
            //     $typeMontant
            // );
            // dd($filtre);
            // dd($Liste);
            $stringP = '';
            $Total_Reglement = 0;
            $Total_Reste = 0;
            $Total_Montant = 0;
            foreach($Liste as $data){

                $client = $repoEntre->findOneByNom($data["entreprise"]);
                $id_client = "";
                if($client){
                    $id_entre = $client->getId();
                }
                // <span class="span_check_unite" style="display:none;">
                //                 <input type="checkbox" class="checkbox_unite" checked data-check = "true">
                //             </span> les petit boutons
                    $stringP .= '

                <div>
                    <div class="t_body_row">
                        <div class="td_long" colspan="9">
                            
                            <a href="/tropical_wood/show_entreprise/' . $id_entre. '" target=_blank>
                                <span>' . $data["entreprise"] . '</span>
                            </a>
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
                            $date_conf = "";
                            $date_fact = "";
                            if($item->getDateConfirmation() != null ){
                                $date_conf = $item->getDateConfirmation()->format("d-m-Y");
                            }
                            if($item->getDateFacture() != null ){
                                $date_fact = $item->getDateFacture()->format("d-m-Y");
                            }
                            // debut balise parent pour liste des pf
                            $string1 .= '
                                <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;"
                                > 
                            ';
                            // ajout de btn suppr ligne pf uniquement pour super_admin
                            if($profile_user == "super_admin"){
                                $string1 .= '
                                    <div class="tw_client">
                                        <span>' . $item->getIdPro() . '</span>
                                        <a href="#" data-id = "'. $item->getId() .'" class="btn_delete_pf">
                                            <span class="fa fa-trash-o"></span>
                                        </a>
                                    </div>
                                ';
                            }
                            else if($profile_user != "super_admin"){
                                $string1 .= '
                                    <div class="tw_client">
                                        <span>' . $item->getIdPro() . '</span>
                                    </div>
                                ';
                            }
                            $string1 .= '
                                    <div class="tw_details">
                                        <span>'. $item->getDetail() .'</span>
                                    </div>
                                    <div class="tw_type_trans">
                                        <span class="value">'. $item->getTypeTransaction() .'</span>
                                    </div>
                                    <div class="tw_etat_prod">
                                        <span class="value">'.$item->getEtatProduction(). '</span>
                                    </div>
                                    <div class="tw_etape_prod">
                                        <span class="value">' . $item->getEtapeProduction() . '%</span>
                                    </div>
                                    <div class="tw_paiement">
                                        <span class="montant value">' . $item->getTotalReglement() . '</span>
                                    </div>
                                    <div class="tw_reste">
                                        <span class="montant">
                                            '. $reste .'
                                        </span>
                                    </div>
                                    <div class="tw_montant_total">
                                        <span class="montant">'. $item->getMontantTotal() .'</span>
                                    </div>
                                    <div class="tw_date_conf">
                                        <span>
                                        '. $date_conf .'
                                        </span>
                                    </div>
                                    <div class="tw_date_facture">
                                        <span>
                                            '. $date_fact .'
                                        </span>
                                    </div>
                                </div>
                            '; 
                        }
                    
                        $stringP .= $string1 ;
                    
                    $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div class="tw_client">
                                        <span>Sous-total</span> 
                                    </div>
                                    <div class="tw_details">
                                        <span></span>
                                    </div>
                                    <div class="tw_type_trans">
                                        <span></span>
                                    </div>
                                    <div class="tw_etat_prod">
                                        <span></span>
                                    </div>
                                    <div class="tw_etape_prod">
                                        <span></span>
                                    </div>
                                    <div class="tw_paiement">
                                        <span class="montant value total_paiement">
                                            '. $total_reglement . '
                                        </span>
                                    </div>
                                    <div class="tw_reste">
                                        <span class="montant value total_reste" >' . $total_reste . '</span>
                                    </div>
                                    <div class="tw_montant_total">
                                   
                                        <span class="montant value total_montant">'. $total_montant . '</span>
                                    </div>
                                    <div class="tw_date_conf">
                                        <span></span>
                                    </div>
                                    <div class="tw_date_facture">
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
                        <div class="t_footer_row sous_tab_body total_content">
                            <div class="tw_client">
                                <span>Total</span>
                            </div>
                            <div class="tw_details">
                                <span></span>
                            </div>
                            <div class="tw_type_trans">
                                <span></span>
                            </div>
                            <div class="tw_etat_prod">
                                <span></span>
                            </div>
                            <div class="tw_etape_prod">
                                <span></span>
                            </div>
                            <div class="tw_paiement" id="parent_total_paiement">
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div class="tw_reste" id="parent_total_reste">
                                <span class="montant value" id="total_reste" data = "' . $Total_Reste . '">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div class="tw_montant_total" id="parent_total_montant">
                                <span class="montant value" id="total_montant">
                                        ' .$Total_Montant . '
                                </span>
                            </div>
                            <div class="tw_date_conf">
                                <span></span>
                            </div>
                            <div class="tw_date_facture">
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
    public function search_ajax_btn_ok(Request $request, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
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
                $client = $repoEntre->findOneByNom($data["entreprise"]);
                $id_client = "";
                if ($client) {
                    $id_entre = $client->getId();
                }

                $stringP .= '

                <div>
                    <div class="t_body_row">
                        <div class="td_long" colspan="9">
                           
                            <a href="/tropical_wood/show_entreprise/' . $id_entre . '" target=_blank>
                                <span>' . $data["entreprise"] . '</span>
                            </a>
                        </div>
                        <button clicked="false" class="btn_drop_data btn btn-warning btn-xs"><span class="fa fa-angle-up"></span></button>
                    </div>
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
                    $date_conf = "";
                    $date_fact = "";
                    if ($item->getDateConfirmation() != null) {
                        $date_conf = $item->getDateConfirmation()->format("d-m-Y");
                    }
                    if ($item->getDateFacture() != null) {
                        $date_fact = $item->getDateFacture()->format("d-m-Y");
                    }
                    $string1 .= '
                                <div class="t_body_row sous_tab_body div_for_droping"
                                    style="display:none !important;"
                                > 
                                    <div class="tw_client">
                                        <span>' . $item->getIdPro() . '</span>
                                    </div>
                                    <div class="tw_details">
                                        <span>' . $item->getDetail() . '</span>
                                    </div>
                                    <div class="tw_type_trans">
                                        <span class="value">' . $item->getTypeTransaction() . '</span>
                                    </div>
                                    <div class="tw_etat_prod">
                                        <span class="value">' . $item->getEtatProduction() . '</span>
                                    </div>
                                    <div class="tw_etape_prod">
                                        <span class="value">' . $item->getEtapeProduction() . '%</span>
                                    </div>
                                    <div class="tw_paiement">
                                        <span class="montant value">' . $item->getTotalReglement() . '</span>
                                    </div>
                                    <div class="tw_reste">
                                        <span class="montant">
                                            ' .$reste . '
                                        </span>
                                    </div>
                                    <div class="tw_montant_total">
                                        <span class="montant">' . $item->getMontantTotal() . '</span>
                                    </div>
                                    <div class="tw_date_conf">
                                        <span>
                                        ' . $date_conf . '
                                        </span>
                                    </div>
                                    <div class="tw_date_facture">
                                        <span>
                                            '. $date_fact .'
                                        </span>
                                    </div>
                                </div>
                            ';
                }
                $stringP .= $string1;

                $stringP .= '
                                <div class="t_body_row sous_tab_body sous_total_content">
                                    <div class="tw_client">
                                        <span>Sous-total</span> 
                                    </div>
                                    <div class="tw_details">
                                        <span></span>
                                    </div>
                                    <div class="tw_type_trans">
                                        <span></span>
                                    </div>
                                    <div class="tw_etat_prod">
                                        <span></span>
                                    </div>
                                    <div class="tw_etape_prod">
                                        <span></span>
                                    </div>
                                    <div class="tw_paiement">
                                        <span class="montant value total_paiement" >
                                            ' .$total_reglement . '
                                        </span>
                                    </div>
                                    <div class="tw_reste">
                                        <span class="montant value total_reste">' . $total_reste . '</span>
                                    </div>
                                    <div class="tw_montant_total">
                                   
                                        <span class="montant value total_montant">' . $total_montant . '</span>
                                    </div>
                                    <div class="tw_date_conf">
                                        <span></span>
                                    </div>
                                    <div class="tw_date_facture">
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
                        <div class="t_footer_row sous_tab_body total_content">
                            <div class="tw_client">
                                <span>Total</span>
                            </div>
                            <div class="tw_details">
                                <span></span>
                            </div>
                            <div class="tw_type_trans">
                                <span></span>
                            </div>
                            <div class="tw_etat_prod">
                                <span></span>
                            </div>
                            <div class="tw_etape_prod">
                                <span></span>
                            </div>
                            <div class="tw_paiement" id="parent_total_paiement">
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div class="tw_reste" id="parent_total_reste">
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div>
                            <div class="tw_montant_total" id="parent_total_montant">
                                <span class="montant value" id="total_montant">
                                        ' . $Total_Montant . '
                                </span>
                            </div>
                            <div class="tw_date_conf">
                                <span></span>
                            </div>
                            <div class="tw_date_facture">
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
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
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
    public function liste_client_tw(CacheInterface $cache_demande, SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {
        $data_session = $session->get('hotel');
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "tropical_wood";
        $datasAsc = $repoTrop->findAllGroupedAsc();
        $Liste = [];
        //dd($datasAsc);
        // on a recopié les code pour la liste des entreprise après upload file pour eviter de refaire les tris ASC et DESC
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
     * @Route("/tropical_wood/liste_changement_pf", name="liste_changement_pf")
     */
    public function liste_changement_pf(CacheInterface $cache_demande, 
                                        SessionInterface $session, 
                                        Request $request, Services $services, 
                                        EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop,
                                        ChangementAfterImportRepository $repoChangement,
                                        ClientUpdatedRepository $repoClientUp,
                                        IntervalChangePFRepository $repoInterval,
                                        ListePFUpdatedRepository $repoListePF
                                        )
    {   
        $all_interval = $repoInterval->findAll();
        // $clientsUp = $all_interval[0]->getClientUpdateds();
        // dd($clientsUp[0]->getId());
        
        $Tableau_recap_changement = [];
        
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        if ($data_session == null) {
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "tropical_wood";

        foreach ($all_interval as $Item) {
            $tab_interval = [
                "date_interval" => $Item->getIntervalle(),
                "liste_client"  => []
            ];

            // tous les clients de cet intervalle

            //$clients = $repoClientUp->findDistinctClientByInterval($Item->getIntervalle());
            $clients = $Item->getClientUpdateds();
            
            // tous les listes pf 
           
            foreach($clients as $client){
                //$listePFUpdateds = $repoListePF->findDistinctListePFUpByInterval($Item->getIntervalle(), $client->getNom());
               
                $pfsUp = $client->getListePFUpdateds();
                //dd($pfsUp[0]);
                //array_push($tab_interval['liste_client'], $tab_client);
                $tab_client = [
                    "nom_client" => $client->getNom(),
                    "liste_pf" => []
                ];
                foreach ($pfsUp as $pf) {
                    //dd("tt");
                    //$changes = $repoChangement->findChangeByInterval($Item->getIntervalle(), $pf->getIdPro());
                    $changes = $pf->getChangementAfterImports();
                    // array_push($changements, $changes);
                    $tab_pf = [
                        "id_pro"    => $pf->getIdPro(),
                        "liste_changement"  => []
                    ];
                    foreach($changes as $change){
                        /*
                          $tab_changement = [
                            "nom"   => $change['nom'],
                            "last_data" => $change['last_data'],
                            "next_data" => $change['next_data']
                        ];
                         */
                        $tab_changement = [
                            "nom"   => $change->getNom(),
                            "last_data" => $change->getLastData(),
                            "next_data" => $change->getNextData()
                        ];
                        array_push($tab_pf["liste_changement"], $tab_changement);
                    }
                    array_push($tab_client['liste_pf'], $tab_pf);
                }
                array_push($tab_interval['liste_client'], $tab_client);
                
            }
            
            array_push($Tableau_recap_changement, $tab_interval);
            
        }
        //dd($Tableau_recap_changement);
        // alamina le tableau
        if(count($Tableau_recap_changement) > 0){
           
            for($j = 0; $j<count($Tableau_recap_changement); $j++){
                $clients = [];
                $tab_clients = [];
                foreach($Tableau_recap_changement[$j]['liste_client'] as $client){
                    if(!in_array($client['nom_client'], $clients)){
                        array_push($clients, $client['nom_client']);
                        array_push($tab_clients, $client);
                    }else{
                        // alaina ny liste pf-any de asitrika any @le efa mitovy 
                        for($i = 0; $i<count($tab_clients); $i++){
                            if($tab_clients[$i]['nom_client'] == $client['nom_client']){
                                   
                                array_push($tab_clients[$i]['liste_pf'], [
                                    "id_pro" => $client['liste_pf'][0]['id_pro'],
                                    "liste_changement" => $client['liste_pf'][0]['liste_changement']
                                ]);
                            }
                            
                        }
                    }
                }
                $Tableau_recap_changement[$j]['liste_client'] = $tab_clients;
                // dd( $Tableau_recap_changement[$j]['liste_client']);
                //dd($tab_clients);
                // apetaka @le tab ngeza be sisa le tab_clients
            }
        }
        else if(count($Tableau_recap_changement) == 0){
            $Tableau_recap_changement = [];
        }
        
        //dd($Tableau_recap_changement);
        return $this->render('page/liste_changement.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"                   => "li_liste_changement",
            "Tableau"           => $Tableau_recap_changement,
        ]);
    }

    /**
     * @Route("/tropical_wood/show_entreprise/{id_entreprise}", name="show_entreprise")
     */
    public function show_entreprise($id_entreprise, SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
    {   
        //dd($nom_entreprise);
        $data_session = $session->get('hotel');
        if($data_session == null){
            return $this->redirectToRoute("app_logout");
        }
        $data_session['pseudo_hotel'] = "tropical_wood";

        $nom_entreprise = $repoEntre->find($id_entreprise)->getNom();
        $today = new \DateTime();
        $now = $today->format("Y-m-d");

        $All_data_ne = $repoTrop->findBy(
            ['entreprise'    => $nom_entreprise],
            ['montant_total' => 'DESC']
        );
        //dd($All_data_ne);
        
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
        

        //dd($tab_trans_enc);
        return $this->render('page/show_entreprise.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "id_page"           => "li_entreprise_contact",
            "datas"             => $All_data_ne,
            "id_client"        => $id_entreprise,
            "nom_client"        => $nom_entreprise,
            "chiffre_daf"       => $ca,
            "chiffre_daf_enc"   => $cae,
            "chiffre_daf_a_enc" => $caae,
            "tab_trans_enc"     => $tab_trans_enc,
            "tab_trans_ter"     => $tab_trans_ter,
            "liste_pf"          => $liste_pf,
            "today"             => $now,
        ]);
    }

    /**
     * @Route("/tropical_wood/lister_transaction_enc", name = "lister_transaction_enc")
     */
    public function lister_transaction_enc(Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id_entreprise = $request->get('id_client');
           
            $tri_total_reglement = $request->get('tri_total_reglement');
            $tri_reste = $request->get('tri_reste');
            $tri_total_montant = $request->get('tri_total_montant');
         
            $nom_entreprise = $repoEntre->find($id_entreprise)->getNom();
            $All_data_ne = [];
            //dd($nom_entreprise);
            if ($tri_total_reglement != null) {
                $All_data_ne = $repoTrop->findBy(
                    ['entreprise'    => $nom_entreprise],
                    ['total_reglement' => $tri_total_reglement]

                );
            }
            if ($tri_total_montant != null) {
                $All_data_ne = $repoTrop->findBy(
                    ['entreprise'    => $nom_entreprise],
                    ['montant_total' => $tri_total_montant]

                );
            }

            if ($tri_reste != null) {
                $All_data_ne = $repoTrop->findBy(
                    ['entreprise'    => $nom_entreprise],
                    ['reste' => $tri_reste]
                );
            }
            
            // Calcul des chiffres d'affaire

            $ca = 0;
            $cae = 0;
            $caae = 0;

            foreach ($All_data_ne as $item) {
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $caae = $caae + $r;
            }

            // les donnée pour transaction en cours et celles qui sont terminées
            $tab_trans_enc = [];
            $tab_trans_ter = [];
            $tab_facture_en_att = [];
            foreach ($All_data_ne as $item) {
                
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $etat_production = mb_strtolower($item->getEtatProduction(), 'UTF-8');
                
                if ($r > 0 && $etat_production == 'facturé') {

                    array_push($tab_facture_en_att, $item);

                }else if($r > 0 && $etat_production != 'facturé'){
                    
                    array_push($tab_trans_enc, $item);
                    
                }else if($r <= 0){
                    
                    array_push($tab_trans_ter, $item);
                }
            }
            $html = '';
            if(count($tab_trans_enc) >= 11){
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr block_tr_scroll">
                ';
            }
            else if(count($tab_trans_enc) < 11){
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr">
                ';
            }

            $total_total_montant = 0;
            $total_reste = 0;
            $total_total_reglement = 0;
            foreach($tab_trans_enc as $item){
                // date 
                $date_conf = "";
                $date_facture = "";
                $date1 = $item->getDateConfirmation();
                $date2 = $item->getDateFacture();
                if($date1){
                    $date_conf = $date1->format('d/m/Y');
                }
                if ($date2) {
                    $date_facture = $date2->format('d/m/Y');
                }
               
                $total_montant = $item->getMontantTotal();
                $total_reglement = $item->getTotalReglement();
                $reste = $total_montant - $total_reglement;
                // totals

                $total_reste += $reste;
                $total_total_montant += $total_montant;
                $total_total_reglement += $total_reglement;
                $html .= '
                
                    <div class="t_body_row sous_tab_body">
                        <div class="tw_client_trans">
                            <span>' . $item->getIdPro() . '</span>
                        </div>
                        <div class="tw_details_trans">
                            <span>' . $item->getDetail() . '</span>
                        </div>
                        <div class="tw_type_trans_trans">
                            <span class="value">' . $item->getTypeTransaction() . '</span>
                        </div>
                        <div class="tw_etat_prod_trans">
                            <span class="value">' . $item->getEtatProduction() . '</span>
                        </div>
                        <div class="tw_etape_prod_trans">
                            <span class="value">' . $item->getEtapeProduction() . '%</span>
                        </div>
                        <div class="tw_paiement_trans">
                            <span class="montant value">' . $item->getTotalReglement() . '</span>
                        </div>
                        <div class="tw_reste_trans">
                            <span class="montant">
                                ' . $reste . '
                            </span>
                        </div>
                        <div class="tw_montant_total_trans">
                            <span class="montant">' . $total_montant . '</span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_conf . '
                            </span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_facture . '
                            </span>
                        </div>
                    </div>
                
                ';
            }

            $html .= '
                </div>
            </div>
            <div class="t_footer_row sous_tab_body total_content t_footer_trans">
                <div class="tw_client_trans">
                    <span>Total</span>
                </div>
                <div class="tw_details_trans">
                    <span></span>
                </div>
                <div class="tw_type_trans_trans">
                    <span></span>
                </div>
                <div class="tw_etat_prod_trans">
                    <span></span>
                </div>
                <div class="tw_etape_prod_trans">
                    <span></span>
                </div>
                <div class="tw_paiement_trans ">
                    <span class="montant value" id="total_paiement">
                    ' . $total_total_reglement . '
                    </span>
                </div>
                <div class="tw_reste_trans">
                    <span class="montant value" id="total_reste">
                        ' . $total_reste . '
                    </span>
                </div>
                <div class="tw_montant_total_trans">
                    <span class="montant value" id="total_montant">
                        ' . $total_total_montant . '
                    </span>
                </div>
                <div class="tw_date_conf_trans">
                    <span></span>
                </div>
                <div class="tw_date_facture_trans">
                    <span></span>
                </div>
            </div>
            ';


            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        } 
        
        return $response;
    }

    /**
     * @Route("/tropical_wood/lister_transaction_terminer", name = "lister_transaction_terminer")
     */
    public function lister_transaction_terminer(Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id_entreprise = $request->get('id_client');

            $nom_entreprise = $repoEntre->find($id_entreprise)->getNom();

            //dd($nom_entreprise);

            $All_data_ne = $repoTrop->findBy(
                ['entreprise'    => $nom_entreprise],
                ['montant_total' => 'DESC']

            );

            // Calcul des chiffres d'affaire
            
            $ca = 0;
            $cae = 0;
            $caae = 0;

            foreach ($All_data_ne as $item) {
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $caae = $caae + $r;
            }

            // les donnée pour transaction en cours et celles qui sont terminées
            $tab_trans_enc = [];
            $tab_trans_ter = [];
            $tab_facture_en_att = [];
            foreach ($All_data_ne as $item) {
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $etat_production = $item->getEtatProduction();
                
                if ($r > 0 && $etat_production == 'facturé') {

                    array_push($tab_facture_en_att, $item);

                }else if($r > 0 && $etat_production != 'facturé'){

                    array_push($tab_trans_enc, $item);
                    
                }else if($r <= 0){
                    
                    array_push($tab_trans_ter, $item);
                }
            }
            $html = '';
            if (count($tab_trans_ter) >= 11) {
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr block_tr_scroll">
                ';
            } else if (count($tab_trans_ter) < 11) {
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr">
                ';
            }

            $total_total_montant = 0;
            $total_reste = 0;
            $total_total_reglement = 0;
            foreach ($tab_trans_ter as $item) {
                // date 
                $date_conf = "";
                $date_facture = "";
                $date1 = $item->getDateConfirmation();
                $date2 = $item->getDateFacture();
                if ($date1) {
                    $date_conf = $date1->format('d/m/Y');
                }
                if ($date2) {
                    $date_facture = $date2->format('d/m/Y');
                }

                $total_montant = $item->getMontantTotal();
                $total_reglement = $item->getTotalReglement();
                $reste = $total_montant - $total_reglement;
                // totals

                $total_reste += $reste;
                $total_total_montant += $total_montant;
                $total_total_reglement += $total_reglement;
                // à retenir etape en % etat text ex facturé...
                $html .= '
                
                    <div class="t_body_row sous_tab_body">
                        <div class="tw_client_trans">
                            <span>' . $item->getIdPro() . '</span>
                        </div>
                        <div class="tw_details_trans">
                            <span>' . $item->getDetail() . '</span>
                        </div>
                        <div class="tw_type_trans_trans">
                            <span class="value">' . $item->getTypeTransaction() . '</span>
                        </div>
                        <div class="tw_etat_prod_trans">
                            <span class="value">' . $item->getEtatProduction() . '</span>
                        </div>
                        <div class="tw_etape_prod_trans">
                            <span class="value">' . $item->getEtapeProduction() . '%</span>
                        </div>
                        <div class="tw_paiement_trans">
                            <span class="montant value">' . $item->getTotalReglement() . '</span>
                        </div>
                        <div class="tw_reste_trans">
                            <span class="montant">
                                ' . $reste . '
                            </span>
                        </div>
                        <div class="tw_montant_total_trans">
                            <span class="montant">' . $total_montant . '</span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_conf . '
                            </span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_facture . '
                            </span>
                        </div>
                    </div>
                
                ';
            }

            $html .= '
                </div>
            </div>
            <div class="t_footer_row sous_tab_body total_content t_footer_trans">
                <div class="tw_client_trans">
                    <span>Total</span>
                </div>
                <div class="tw_details_trans">
                    <span></span>
                </div>
                <div class="tw_type_trans_trans">
                    <span></span>
                </div>
                <div class="tw_etat_prod_trans">
                    <span></span>
                </div>
                <div class="tw_etape_prod_trans">
                    <span></span>
                </div>
                <div class="tw_paiement_trans">
                    <span class="montant value" id="total_paiement">
                    ' . $total_total_reglement . '
                    </span>
                </div>
                <div class="tw_reste_trans">
                    <span class="montant value" id="total_reste">
                        ' . $total_reste . '
                    </span>
                </div>
                <div class="tw_montant_total_trans">
                    <span class="montant value" id="total_montant">
                        ' . $total_total_montant . '
                    </span>
                </div>
                <div class="tw_date_conf_trans">
                    <span></span>
                </div>
                <div class="tw_date_facture_trans">
                    <span></span>
                </div>
            </div>
            ';


            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }

        return $response;
    }

    /**
     * @Route("/tropical_wood/lister_facture_en_att", name = "lister_facture_en_att")
     */
    public function lister_facture_en_att(Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id_entreprise = $request->get('id_client');

            $nom_entreprise = $repoEntre->find($id_entreprise)->getNom();

            //dd($nom_entreprise);

            $All_data_ne = $repoTrop->findBy(
                ['entreprise'    => $nom_entreprise],
                ['montant_total' => 'DESC']

            );

            // Calcul des chiffres d'affaire
            
            $ca = 0;
            $cae = 0;
            $caae = 0;

            foreach ($All_data_ne as $item) {
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $caae = $caae + $r;
            }

            // les donnée pour transaction en cours et celles qui sont terminées
            $tab_trans_enc = [];
            $tab_facture_en_att = [];
            $tab_trans_ter = [];
            foreach ($All_data_ne as $item) {
                $ca = $ca + $item->getMontantTotal();
                $cae = $cae + $item->getTotalReglement();
                $r = $item->getMontantTotal() - $item->getTotalReglement();
                $etat_production = $item->getEtatProduction();
                
                if ($r > 0 && $etat_production == 'facturé') {

                    array_push($tab_facture_en_att, $item);

                }else if($r > 0 && $etat_production != 'facturé'){

                    array_push($tab_trans_enc, $item);
                    
                }else if($r <= 0){
                    
                    array_push($tab_trans_ter, $item);
                }
            }
            $html = '';
            if (count($tab_facture_en_att) >= 11) {
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr block_tr_scroll">
                ';
            } else if (count($tab_facture_en_att) < 11) {
                $html .= '
                    <div class="dr_tab_trans">
                    <div class="block_all_tr">
                ';
            }

            $total_total_montant = 0;
            $total_reste = 0;
            $total_total_reglement = 0;
            foreach ($tab_facture_en_att as $item) {
                // date 
                $date_conf = "";
                $date_facture = "";
                $date1 = $item->getDateConfirmation();
                $date2 = $item->getDateFacture();
                if ($date1) {
                    $date_conf = $date1->format('d/m/Y');
                }
                if ($date2) {
                    $date_facture = $date2->format('d/m/Y');
                }

                $total_montant = $item->getMontantTotal();
                $total_reglement = $item->getTotalReglement();
                $reste = $total_montant - $total_reglement;
                // totals

                $total_reste += $reste;
                $total_total_montant += $total_montant;
                $total_total_reglement += $total_reglement;
                // à retenir etape en % etat text ex facturé...
                $html .= '
                
                    <div class="t_body_row sous_tab_body">
                        <div class="tw_client_trans">
                            <span>' . $item->getIdPro() . '</span>
                        </div>
                        <div class="tw_details_trans">
                            <span>' . $item->getDetail() . '</span>
                        </div>
                        <div class="tw_type_trans_trans">
                            <span class="value">' . $item->getTypeTransaction() . '</span>
                        </div>
                        <div class="tw_etat_prod_trans">
                            <span class="value">' . $item->getEtatProduction() . '</span>
                        </div>
                        <div class="tw_etape_prod_trans">
                            <span class="value">' . $item->getEtapeProduction() . '%</span>
                        </div>
                        <div class="tw_paiement_trans">
                            <span class="montant value">' . $item->getTotalReglement() . '</span>
                        </div>
                        <div class="tw_reste_trans">
                            <span class="montant">
                                ' . $reste . '
                            </span>
                        </div>
                        <div class="tw_montant_total_trans">
                            <span class="montant">' . $total_montant . '</span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_conf . '
                            </span>
                        </div>
                        <div class="tw_date_conf_trans">
                            <span>
                                ' . $date_facture . '
                            </span>
                        </div>
                    </div>
                
                ';
            }

            $html .= '
                </div>
            </div>
            <div class="t_footer_row sous_tab_body total_content t_footer_trans">
                <div class="tw_client_trans">
                    <span>Total</span>
                </div>
                <div class="tw_details_trans">
                    <span></span>
                </div>
                <div class="tw_type_trans_trans">
                    <span></span>
                </div>
                <div class="tw_etat_prod_trans">
                    <span></span>
                </div>
                <div class="tw_etape_prod_trans">
                    <span></span>
                </div>
                <div class="tw_paiement_trans">
                    <span class="montant value" id="total_paiement">
                    ' . $total_total_reglement . '
                    </span>
                </div>
                <div class="tw_reste_trans">
                    <span class="montant value" id="total_reste">
                        ' . $total_reste . '
                    </span>
                </div>
                <div class="tw_montant_total_trans">
                    <span class="montant value" id="total_montant">
                        ' . $total_total_montant . '
                    </span>
                </div>
                <div class="tw_date_conf_trans">
                    <span></span>
                </div>
                <div class="tw_date_facture_trans">
                    <span></span>
                </div>
            </div>
            ';


            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }

        return $response;
    }
    
    /**
     * @Route("/tropical_wood/entreprise/addAdresse", name = "add_address")
     */
    public function add_address(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $adresse = $request->get('adresse');
            $id_client = $request->get('id_client');

            $entreprise = $repoEntre->find($id_client);
            $entreprise->setAdresse($adresse);
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
    public function add_contact(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager, ContactEntrepriseTWRepository $repoContact)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $id_client = $request->get('id_client');
            $nom_client = $request->get('client');
            $id_contact = $request->get('id_contact');
            $nom_en_contact = $request->get('nom_en_contact');
            // $type = $request->get('type');
            $email = $request->get('email');
            $telephone = $request->get('telephone');

            if($id_contact == ""){
                $ce = new ContactEntrepriseTW();
                $ce->setNomEnContact($nom_en_contact);
                // $ce->setType($type);
                $ce->setEmail($email);
                $ce->setTelephone($telephone);
                $entrepriseO = null;
                if($id_client != null){
                    $entrepriseO = $repoEntre->find($id_client);
                }
                if ($nom_client != null) {
                    $entrepriseO = $repoEntre->findOneByNom($nom_client);
                }
                $ce->setEntreprise($entrepriseO);
                $manager->persist($ce);
                $manager->flush();
            }
            else{
               
                $contact = $repoContact->find($id_contact);
                $contact->setNomEnContact($nom_en_contact);
                // $contact->setType($type);
                $contact->setEmail($email);
                $contact->setTelephone($telephone);
               
                $manager->flush();
            }
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
       
        return $response;
    }

    /**
     * @Route("/tropical_wood/delete_contact", name="delete_contact")
     */
    public function delete_contact(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager, ContactEntrepriseTWRepository $repoContact)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id_client = $request->get('id_client');
            $id_contact = $request->get('id_contact');
            $contact = $repoContact->find($id_contact);
            $manager->remove($contact);
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }
    /**
     * @Route("/tropical_wood/edit_contact", name = "edit_contact")
     */
    public function edit_contact(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager, ContactEntrepriseTWRepository $repoContact)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $id_contact = $request->get('id_contact');
            $id_client = $request->get('id_client');
            $nom_client = $request->get('nom_client');
            if($id_client != null){
                $contact = $repoContact->find($id_contact);
                $html = '

                <form action="">
                    <div class="form-group">
                        <label for="">Contact</label>
                        <input type="text" placeholder="Contact" value="' . $contact->getNomEnContact() . '" id="contact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Email</label>
                        <input type="text" id="email" value="' . $contact->getEmail() . '" placeholder="Adresse@mail...." class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">N° Téléphone</label>
                        <input type="text" id="telephone" value="' . $contact->getTelephone() . '" placeholder="03x xxx" class="form-control">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-default" type="submit" id="button_add_contact" data-id = "' . $contact->getId() . '">
                            <span>Enregistrer</span>
                        </button>
                    </div>
                </form>
            
            ';

                $data = json_encode($html);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
            if ($nom_client != null) {
                $contact = $repoContact->find($id_contact);
                $index = $request->get('index');
                $html = '

                <form action="">
                    <div class="form-group">
                        <label for="">Contact</label>
                        <input type="text" placeholder="Contact" value="' . $contact->getNomEnContact() . '" id="contact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Email</label>
                        <input type="text" id="email" value="' . $contact->getEmail() . '" placeholder="Adresse@mail...." class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">N° Téléphone</label>
                        <input type="text" id="telephone" value="' . $contact->getTelephone() . '" placeholder="03x xxx" class="form-control">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-default" type="submit" id="button_add_contact" data-id = "' . $contact->getId() . '" data-index = "'. intval($index) .'">
                            <span>Enregistrer</span>
                        </button>
                    </div>
                </form>
            
            ';

                $data = json_encode($html);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }

    /**
     * @Route("/tropical_wood/add_remarque", name="add_remarque")
    */
    public function add_remarque(Request $request, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager, RemarqueEntrepriseTWRepository $repoRem)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            
            $date_remarque = $request->get('date_remarque');
            $id_pro = $request->get('id_pro');
            $nom_client = $request->get('client');
            $observation = $request->get('observation');
            $contrainte = $request->get('contrainte');
            $id_client = $request->get('id_client'); 
            $remarque = new RemarqueEntrepriseTW();
            
            $remarque->setConcerne($id_pro);
            $date_remarque = date_create($date_remarque);
            $remarque->setDateRemarque($date_remarque);
            $remarque->setObservation($observation);
            $entreprise = null;
            if($id_client != null){
                $entreprise = $repoEntre->find($id_client);
            }
            if($nom_client != null){
                $entreprise = $repoEntre->findOneByNom($nom_client);
            }
            $remarque->setEntreprise($entreprise);
            // taloha en cours = 0 terminer = 1 en attente = facturé
            if($contrainte == "facturé"){
                
                $remarque->setEtatResultat("facturé");
            }
            if($contrainte == "en cours"){
                $remarque->setEtatResultat("en cours");
            }
            $manager->persist($remarque);
            $manager->flush();

            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/lister_remarque_non", name="lister_remarque_non")
     */
    public function lister_remarque_non(Request $request, EntrepriseTWRepository $repoEntre, RemarqueEntrepriseTWRepository $repoRem)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            
            $id_client = $request->get('id_client');
            $contrainte = $request->get('contrainte');
            // mbola tsy vita ny manasaraka anle contrainte
            $entreprise = $repoEntre->find($id_client);
            $remarques = $entreprise->getRemarqueEntrepriseTWs();
            $html = '
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
            ';
            
            if($contrainte == "en cours"){
                foreach($remarques as $item){
                    $son_etat = $item->getEtatResultat();
                    if($son_etat == "en cours"){ 
                        $date = $item->getDateRemarque()->format('d/m/Y');
                        $concerne = $item->getConcerne();
                        $tab_concerne =explode(",", $concerne);
                        $conc = "";
                        $j = 0;
                        for($i = 0; $i < count($tab_concerne); $i++){
                            
                            $conc .= $tab_concerne[$i].", ";
                            $j++;
                            if($j % 3 == 0){
                                $conc .= "<br>";
                            }
                        }
                        $html .= '
                            <tr class="trb_remarque_a_ter">
                                <td class="t_date_rem"><span>'. $date .'</span></td>
                                <td class="t_concerne_rem"><span>'. $conc . '</span></td>
                                <td class="tbd_remarque_a_ter t_obs_rem">
                                    <p class="observation">
                                        '. $item->getObservation() .'
                                    </p>
                                </td>
                                <td class="t_action_rem">
                                    <div class="liste_action">
                                        <a href="#" data-id = "'. $item->getId() .'" class="edit_remarque">
                                            <span class=" fa fa-check"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        ';
                    } 
                }
            }
            if($contrainte == "facturé"){
                foreach($remarques as $item){
                    $son_etat = $item->getEtatResultat();
                    if($son_etat == "facturé") { 
                        $date = $item->getDateRemarque()->format('d/m/Y');
                        $concerne = $item->getConcerne();
                        $tab_concerne =explode(",", $concerne);
                        $conc = "";
                        $j = 0;
                        for($i = 0; $i < count($tab_concerne); $i++){
                            
                            $conc .= $tab_concerne[$i].", ";
                            $j++;
                            if($j % 3 == 0){
                                $conc .= "<br>";
                            }
                        }
                        $html .= '
                            <tr class="trb_remarque_a_ter">
                                <td class="t_date_rem"><span>'. $date .'</span></td>
                                <td class="t_concerne_rem"><span>'. $conc . '</span></td>
                                <td class="tbd_remarque_a_ter t_obs_rem">
                                    <p class="observation">
                                        '. $item->getObservation() .'
                                    </p>
                                </td>
                                <td class="t_action_rem">
                                    <div class="liste_action">
                                        <a href="#" data-id = "'. $item->getId() .'" class="edit_remarque">
                                            <span class=" fa fa-check"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        ';
                    } 
                }
            }
            //dd($html);

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/lister_remarque_oui", name="lister_remarque_oui")
     */
    public function lister_remarque_oui(Request $request, EntrepriseTWRepository $repoEntre, RemarqueEntrepriseTWRepository $repoRem)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {

            $id_client = $request->get('id_client');
            $entreprise = $repoEntre->find($id_client);
            $remarques = $entreprise->getRemarqueEntrepriseTWs();
            $html = '
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
            
            ';
            foreach ($remarques as $item) {
                $son_etat = $item->getEtatResultat();
                if ($son_etat == "1") { 

                    $date = $item->getDateRemarque()->format('d/m/Y');
                    $concerne = $item->getConcerne();
                    $tab_concerne = explode(",", $concerne);
                    $conc = "";
                    $j = 0;
                    for ($i = 0; $i < count($tab_concerne); $i++) {

                        $conc .= $tab_concerne[$i] . ", ";
                        $j++;
                        if ($j % 3 == 0) {
                            $conc .= "<br>";
                        }
                    }
                    $html .= '
                        <tr class="trb_remarque_a_ter">
                            <td class="t_date_rem"><span>' . $date . '</span></td>
                            <td class="t_concerne_rem"><span>' . $conc . '</span></td>
                            <td class="tbd_remarque_a_ter t_obs_rem">
                                <p class="observation">
                                    ' . $item->getObservation() . '
                                </p>
                            </td>
                            <td class="t_action_rem">
                                <div class="liste_action">
                                    <a href="#" data-id = "' . $item->getId() . '" class="delete_remarque">
                                        <span class="fa fa-trash-o"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    
                    ';
                }
            }


            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/edit_remarque", name ="edit_remarque")
     */
    public function edit_remaque(Request $request, EntrepriseTWRepository $repoEntre, RemarqueEntrepriseTWRepository $repoRem, EntityManagerInterface $manager)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $id_remarque = $request->get('id_remarque');
            $rem = $repoRem->find($id_remarque);
            $rem->setEtatResultat('1');
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/tropical_wood/delete_remarque", name ="delete_remarque")
     */
    public function delete_remarque(Request $request, EntrepriseTWRepository $repoEntre, RemarqueEntrepriseTWRepository $repoRem, EntityManagerInterface $manager)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $id_remarque = $request->get('id_remarque');
            $rem = $repoRem->find($id_remarque);
            $manager->remove($rem);
            $manager->flush();
            $data = json_encode("ok");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
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

            $id_client = $request->get('id_client'); 
            $nom_client =  $request->get('client'); 
            $entreprise = null;         
            if($id_client != null){
                $entreprise = $repoEntre->find($id_client);
            }
            if ($nom_client != null) {
                $entreprise = $repoEntre->findOneByNom($nom_client);
            }
            $html = "";
           
            $contacts = $entreprise->getContactEntrepriseTWs();
            
            if (count($contacts) > 0) {
                $html = '
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';

                foreach ($contacts as $item) {
                    $html .=
                        '
                <tr>
                    <td><span>' . $item->getNomEnContact() . '</span></td>
                    <td><span>' . $item->getEmail() . '</span></td>
                    <td><span>' . $item->getTelephone() . '</span></td>
                    <td>
                        <div class="list_action">
                            <a href="#" class="edit_contact" data-id = "'. $item->getId() .'">
                                <span class="fa fa-edit"></span>
                            </a>
                            <a href="#" class="delete_contact" data-id = "'. $item->getId() .'">
                                <span class="fa fa-trash-o"></span>
                            </a>
                        </div>
                    </td>
                </tr>
            
            ';
                }
            }
            
            
            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        else{
            $entreprise = $repoEntre->findBy(["nom" => "Ministère de l'éducation"]);
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
            $id_client = $request->get('id_client');
           
            $entreprise = $repoEntre->find($id_client);
           
                $html = '
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span>' . $entreprise->getNom() . '</span></td>
                    <td><span>' . $entreprise->getAdresse() . '</span></td>
                    <td>
                        <div class="list_action">
                            <a href="#" class="tab_adress_edit" data-id="' .  $entreprise->getId() . '" 
                                data-adresse ="' . $entreprise->getAdresse() . '"
                            >
                                <span class="fa fa-edit"></span>
                            </a>
                        </div>
                    </td>
                </tr>
            ';
           
            
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
            if(count($entreprise) > 0){
                $entreprise[0]->setAdresse($adresse);
            }
            
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
    public function tri_ajax_entreprise_client(Request $request, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre, EntityManagerInterface $manager)
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
                $request->request->get('date3'),
                $request->request->get('date4'),
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
                    
                    $nom_entre = trim($data["entreprise"]);
                    
                    if ($repoEntre->findOneByNom($nom_entre)) {
                        
                        $id_entre = $repoEntre->findOneByNom($nom_entre)->getId();
                    }
                    
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
                                    <div class="div_ca div_ca_client">
                                        <a href="/tropical_wood/show_entreprise/' . $id_entre . '" class="link_client " target = _blanc>
                                            <span>' . $data["entreprise"] . '</span>
                                        </a>
                                    </div>
                                    <div class="div_ca">
                                        <span class="montant value total_paiement ">
                                            ' . $total_reglement . '
                                        </span>
                                    </div>
                                    <div class="div_ca">
                                        <span class="montant value total_reste ">' . $total_reste . '</span>
                                    </div>
                                    <div class="div_ca">
                                        <span class="montant value total_montant ">' . $total_montant . '</span>
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
                            <div class="div_ca div_ca_client">
                                <span>Total</span>
                            </div>
                            <div class="div_ca">
                                <span class="montant value" id="total_paiement">
                                    ' . $Total_Reglement . '
                                </span>
                            </div>
                            <div class="div_ca">
                                <span class="montant value" id="total_reste">										
                                        ' . $Total_Reste . '
                                </span>
                            </div class="div_ca">
                            <div class="div_ca">
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
    public function tri_ajax_btn_black_tropical_entreprise_client(CacheInterface $cache_liste_client, Request $request, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
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
                $request->request->get('date3'),
                $request->request->get('date4'),
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
                    
                    $nom_entre = $data["entreprise"];
                   
                    $id_entre = $repoEntre->findOneByNom($nom_entre)->getId();

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
                                        <a href="/tropical_wood/show_entreprise/' . $id_entre . '" class="link_client">
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
     * @Route("/tropical_wood/search_ajax_btn_ok/entreprise_client", name = "search_ajax_btn_ok_tropical_entreprise_client")
    */
    public function search_ajax_btn_ok_tropical_entreprise_client(Request $request, DataTropicalWoodRepository $repoTrop, EntrepriseTWRepository $repoEntre)
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
                    
                    $nom_entre = $data["entreprise"];
                    
                    $id_entre = $repoEntre->findOneByNom($nom_entre)->getId();
                    
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
                                        <a href="/tropical_wood/show_entreprise/' . $id_entre . '" class="link_client">
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

    /**
     * @Route("/admin/check_up_impaye/{index}", name = "check_up_impaye")
     */
    public function check_up_impaye($index, Request $request, 
                                    SessionInterface $session,
                                    DataTropicalWoodRepository $repoTrop,
                                    ContactEntrepriseTWRepository $repoEntre) :Response
    {

        $res = $repoTrop->listeResultOfCheckupByEntreprise();
        //dd($res);
        $date = new \Datetime();
        $today = $date->format('Y-m-d');
        $data_session = $session->get('hotel');
        return $this->render("page/check_up_impaye.html.twig",[
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            'tri'               => false,
            'tropical_wood'     => true,
            "today"             => $today,
            "id_page"           => "li_entreprise_contact",
            "resultat"          => $res,
            "index"             => $index,
        ]);
    }

    /**
     * @Route("/admin/maj", name = "maj")
     */
    public function maj(Request $request, 
                        SessionInterface $session,
                        DataTropicalWoodRepository $repoTrop,
                        ContactEntrepriseTWRepository $repoEntre, EntityManagerInterface $manager,
                        ClientUpdatedRepository $repoClientUp) :Response
    {

        $tw = $repoTrop->findAll();
        foreach($tw as $item){
            $item->setEntreprise(mb_strtoupper($item->getEntreprise(), 'UTF-8'));
            $manager->flush();
        }
        $cu = $repoClientUp->findAll();
        foreach($cu as $item){
            $item->setNom(mb_strtoupper($item->getNom(), 'UTF-8'));
            $manager->flush();
        }
        dd($tw);
        return $this->render("page/check_up_impaye.html.twig");
    }



}
