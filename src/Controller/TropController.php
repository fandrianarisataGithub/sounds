<?php

namespace App\Controller;

use App\Services\Services;
use App\Form\FournisseurFileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DataTropicalWoodRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TropController extends AbstractController
{
    /**
     * @Route("/admin/tri/officiel", name="tri.tropical")
     */
    public function officiel(SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {
        
        $data_session = $session->get('hotel');
        $form_add = $this->createForm(FournisseurFileType::class);
        $form_add->handleRequest($request);
        $datas = [];

        // tri

        $message = $request->query->get('message');
        $option = $request->query->get('option');

        // on teste les options 
        $noms = [];
        $les_datas = [];
        switch($option){
            case '1' : 
                $datas = $repoTrop->findBy(['type_transaction' => $message]);
                foreach ($datas as $data) {
                    $entreprise = $data->getEntreprise();
                    if (!in_array($entreprise, $noms)) {
                        array_push($noms, $entreprise);
                        // on select tous les datas de même nom
                        $ens = $repoTrop->findBy(["entreprise" => $entreprise, 'type_transaction' => $message]);
                        array_push($les_datas, $ens);
                    }
                }  
                
            break;
            case '2':
                $datas = $repoTrop->findBy(['etat_production' => $message]);
                foreach ($datas as $data) {
                    $entreprise = $data->getEntreprise();
                    if (!in_array($entreprise, $noms)) {
                        array_push($noms, $entreprise);
                        // on select tous les datas de même nom
                        $ens = $repoTrop->findBy(["entreprise" => $entreprise, 'etat_production' => $message]);
                        array_push($les_datas, $ens);
                    }
                }  

                break;
            case '3':
                $all = $repoTrop->findAll();
                if($message == "Aucun"){
                    $table = [];
                    foreach ($all as $item) {
                        $son_totalreglement = $item->getTotalReglement();
                        $son_montant_total = $item->getMontanttotal();
                        $son_reste = $son_montant_total - $son_totalreglement;
                        if($son_totalreglement == 0){
                            array_push($table, $item);
                        }
                    }

                    array_push($les_datas, $table);
                }
                else if ($message == "partiel") {
                    $table = [];
                    foreach ($all as $item) {
                        $son_totalreglement = $item->getTotalReglement();
                        $son_montant_total = $item->getMontanttotal();
                        $son_reste = $son_montant_total - $son_totalreglement;
                        if (intval($son_reste) > 0 && $son_totalreglement != 0) {
                            array_push($table, $item);
                        }
                    }

                    array_push($les_datas, $table);
                }
                else if ($message == "total") {
                    $table = [];
                    foreach ($all as $item) {
                        $son_totalreglement = $item->getTotalReglement();
                        $son_montant_total = $item->getMontanttotal();
                        $son_reste = $son_montant_total - $son_totalreglement;
                        if (intval($son_reste) == 0 ) {
                            array_push($table, $item);
                        }
                    }
                    array_push($les_datas, $table);
                } 
                break;
        }
        return $this->render('page/tropical_wood.html.twig', [
            "hotel"             => $data_session['pseudo_hotel'],
            "current_page"      => $data_session['current_page'],
            "form_add"          => $form_add->createView(),
            'datas'             => $les_datas,
            'tri'               => true,
        ]);
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
                ]);
            }
            else{
                return $this->redirectToRoute("tropical_wood");
            }
        }
       
    }

    /**
     * @Route("/admin/trop_search", name="trop_search")
     */
    public function trop_search(SessionInterface $session, Request $request, Services $services, EntityManagerInterface $manager, DataTropicalWoodRepository $repoTrop)
    {
        $data_session = $session->get('hotel');
        $form_add = $this->createForm(FournisseurFileType::class);
        $form_add->handleRequest($request);
        $les_datas = [];
        $table = [];
       
        if ($request->request->count() > 0) {
            $type_transaction = $request->request->get('type_transaction');
            $entreprise_contact = $request->request->get('entreprise_contact');           
            // recherche de type_tr
            if($entreprise_contact !="vide"){
               $liste = $repoTrop->searchEntrepriseContact($entreprise_contact);
                array_push($les_datas, $liste);
                return $this->render('page/tropical_wood.html.twig', [
                    "hotel"             => $data_session['pseudo_hotel'],
                    "current_page"      => $data_session['current_page'],
                    "form_add"          => $form_add->createView(),
                    'datas'             => $les_datas,
                    'tri'               => true,
                ]);
            } 
            else if($type_transaction != "vide") {
                $liste = $repoTrop->searchTypeTransaction($type_transaction);
                array_push($les_datas, $liste);
                return $this->render('page/tropical_wood.html.twig', [
                    "hotel"             => $data_session['pseudo_hotel'],
                    "current_page"      => $data_session['current_page'],
                    "form_add"          => $form_add->createView(),
                    'datas'             => $les_datas,
                    'tri'               => true,
                ]);
            }
        }
    }

}
