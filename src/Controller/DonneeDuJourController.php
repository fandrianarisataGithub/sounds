<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\DonneeDuJour;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DonneeDuJourRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DonneeDuJourController extends AbstractController 
{
    /**
     * @Route("/admin/add_donnee_djr", name="add_donnee_djr")
     */
    public function add_donnee_djr(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager)
    {
        $response = new Response();
        
        if ($request->isXmlHttpRequest()) {
            // saika natao ajax fa efa vita tsy ajax 
            // $heb_to = $request->get('heb_to');
            // $heb_ca = $request->get('heb_ca');
            // $res_n_couvert = $request->get('res_n_couvert');
            // $res_ca = $request->get('res_ca');
            // $res_p_dej = $request->get('res_p_dej');
            // $res_dej = $request->get('res_dej');
            // $res_dinner = $request->get('res_dinner');
            // $spa_ca = $request->get('spa_ca');
            // $spa_n_abonne = $request->get('spa_n_abonne');
            // $spa_c_unique = $request->get('spa_c_unique');
            // $crj_direction = $request->get('crj_direction');
            // $crj_service_rh = $request->get('crj_service_rh');
            // $crj_commercial = $request->get('crj_commercial');
            // $crj_comptable = $request->get('crj_comptable');
            // $crj_reception = $request->get('crj_reception');
            // $crj_restaurant = $request->get('crj_restaurant');
            // $crj_spa = $request->get('crj_spa');
            // $crj_s_technique = $request->get('crj_s_technique');
            // $crj_litiges = $request->get('crj_litiges');
            // $created_at = new \DateTime();
            // $pseudo_hotel = $request->get('pseudo_hotel');

            

            $data = json_encode("deleted");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/historique_heb", name="historique_heb")
     */
    public function historique_heb(Services $services, Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        
        if ($request->isXmlHttpRequest()) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            if($date1 != "" && $date2 != ""){
                $pseudo_hotel = $request->request->get('pseudo_hotel');
                $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $l_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $historique = $request->request->get('historique');
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);
                $ddjs = $repoDdj->findAll();
                $tab = [];
                foreach ($ddjs as $item) {
                    $son_id_hotel = $item->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab, $item);
                    }
                }
                $tab_aff = [];
                $t = [];
                foreach ($tab as $item) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $c = $item->getCreatedAt();
                    $c_s = $c->format("Y-m-d");
                    //dd($his_al_dates);
                    for ($i = 0; $i < count($all_date_asked); $i++) {
                        if ($c_s == $all_date_asked[$i]) {
                            array_push($tab_aff, $item);
                        }
                    }
                }
                
                foreach ($tab_aff as $item) {

                    array_push($t, ['<div>' . $item->getHebTo() . '<span class="unite">%</span></div>', '<div>' . $item->getHebCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '<div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_disoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
            
            else{
                $pseudo_hotel = $request->get('pseudo_hotel');
                $ddj = new DonneeDuJour();
                $current_id_hotel = $repoHotel->findOneByPseudo($pseudo_hotel)->getId();
                //dd($current_id_hotel);
                $all_ddj = $repoDdj->findAll();
                // dd($all_ddj);
                $tab_ddj = [];
                $t = [];
                foreach ($all_ddj as $d) {
                    $son_id_hotel = $d->getHotel()->getId();
                    //dd($son_id_hotel);
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab_ddj, $d);
                    }
                }
                //    dd($tab_ddj);
                foreach ($tab_ddj as $item) {

                    array_push($t, ['<div>' . $item->getHebTo() . '<span class="unite">%</span></div>', '<div>' . $item->getHebCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '<div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_form_disoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }    
        }
        
        
    }

    /**
     * @Route("/profile/historique_res", name="historique_res")
     */
    public function historique_res(Services $services, Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            if($date1 != "" && $date2 != ""){
                $pseudo_hotel = $request->request->get('pseudo_hotel');
                $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $l_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $historique = $request->request->get('historique');
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);
                $ddjs = $repoDdj->findAll();
                $tab = [];
                foreach ($ddjs as $item) {
                    $son_id_hotel = $item->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab, $item);
                    }
                }
                $tab_aff = [];
                $t = [];
                foreach ($tab as $item) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $c = $item->getCreatedAt();
                    $c_s = $c->format("Y-m-d");
                    //dd($his_al_dates);
                    for ($i = 0; $i < count($all_date_asked); $i++) {
                        if ($c_s == $all_date_asked[$i]) {
                            array_push($tab_aff, $item);
                        }
                    }
                }

                foreach ($tab_aff as $item) {

                    array_push($t, ['<div>' . $item->getResCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getResPDej() . '<span class="unite">Couverts</span></div>', '<div>' . $item->getResDej() . '<span class="unite">Couverts</span></div>', '<div>' . $item->getResDinner() . '<span class="unite">Couverts</span></div>', '<div>' . ($item->getResPDej() + $item->getResDinner() + $item->getResDej()) . '<span class="unite">Couverts</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
            else{
                $pseudo_hotel = $request->get('pseudo_hotel');
                $ddj = new DonneeDuJour();
                $current_id_hotel = $repoHotel->findOneByPseudo($pseudo_hotel)->getId();
                //dd($current_id_hotel);
                $all_ddj = $repoDdj->findAll();
                // dd($all_ddj);
                $tab_ddj = [];
                $t = [];
                foreach ($all_ddj as $d) {
                    $son_id_hotel = $d->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab_ddj, $d);
                    }
                }
                //    dd($tab_ddj);
                foreach ($tab_ddj as $item) {

                    array_push($t, ['<div>' . $item->getResCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getResPDej() . '<span class="unite">Couverts</span></div>', '<div>' . $item->getResDej() . '<span class="unite">Couverts</span></div>', '<div>' . $item->getResDinner() . '<span class="unite">Couverts</span></div>', '<div>' . ($item->getResPDej() + $item->getResDinner() + $item->getResDej()) . '<span class="unite">Couverts</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }

    /**
     * @Route("/profile/historique_spa", name="historique_spa")
     */
    public function historique_spa(Services $services, Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {
            $date1 = $request->get('date1');
            $date2 = $request->get('date2');
            if ($date1 != "" && $date2 != "") {
                $pseudo_hotel = $request->request->get('pseudo_hotel');
                $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $l_hotel->getId();
                $date1 = date_create($date1);
                $date2 = date_create($date2);
                $historique = $request->request->get('historique');
                $all_date_asked = $services->all_date_between2_dates($date1, $date2);
                $ddjs = $repoDdj->findAll();
                $tab = [];
                foreach ($ddjs as $item) {
                    $son_id_hotel = $item->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab, $item);
                    }
                }
                $tab_aff = [];
                $t = [];
                foreach ($tab as $item) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $c = $item->getCreatedAt();
                    $c_s = $c->format("Y-m-d");
                    //dd($his_al_dates);
                    for ($i = 0; $i < count($all_date_asked); $i++) {
                        if ($c_s == $all_date_asked[$i]) {
                            array_push($tab_aff, $item);
                        }
                    }
                }

                foreach ($tab_aff as $item) {

                    array_push($t, ['<div>' . $item->getSpaCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getSpaNAbonne() . '<span class="unite">Abonnés</span></div>', '<div>' . $item->getSpaCUnique() . '<span class="unite">Clients</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
           else{
                $pseudo_hotel = $request->get('pseudo_hotel');
                $ddj = new DonneeDuJour();
                $current_id_hotel = $repoHotel->findOneByPseudo($pseudo_hotel)->getId();
                //dd($current_id_hotel);
                $all_ddj = $repoDdj->findAll();
                // dd($all_ddj);
                $tab_ddj = [];
                $t = [];
                foreach ($all_ddj as $d) {
                    $son_id_hotel = $d->getHotel()->getId();
                    //dd($son_id_hotel);
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab_ddj, $d);
                    }
                }
                //    dd($tab_ddj);
                foreach ($tab_ddj as $item) {

                    array_push($t, ['<div>' . $item->getSpaCa() . '<span class="unite">Ar</span></div>', '<div>' . $item->getSpaNAbonne() . '<span class="unite">Abonnés</span></div>', '<div>' . $item->getSpaCUnique() . '<span class="unite">Clients</span></div>', '<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
           }
        }
        
    }

    /**
     * @Route("/admin/suprimer_ddj", name="suprimer_ddj")
     */
    public function suprimer_ddj(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();

        if ($request->isXmlHttpRequest()) {

            $pseudo_hotel = $request->get('pseudo_hotel');
            $ddj_id = $request->get('ddj_id');

            $ddj = $repoDdj->find($ddj_id);
            $manager->remove($ddj);
            $manager->flush();

            $data = json_encode("deleted");
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
       
    }
    /**
     * @Route("/profile/checkout_h_heb", name = "checkout_h_heb")
     */
    public function checkout_h_heb(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
           
            $id = $request->get('ddj_id');
            $ddj = $repoDdj->find($id);
            $html = '';
            $html.='
            
                <form action="">
                    <div class="form-group">
                        <label for="n_date_depart">
                            Taux d occupation (%) :
                        </label>
                        <input type="text" id = "modal_modif_heb_to" class="form-control histo_pourcent" onkeyup="handleChange(this);" value ="'. $ddj->getHebTo() .'">
                    </div>
                    <div class="form-group">
                        <label for="n_date_depart">Chiffre d affaire (Ariary) :
                        </label>
                        <input type="text" id = "modal_modif_heb_ca" class="form-control ca" value = "'. $ddj->getHebCa() .'">
                    </div>
                    <div class="form-group">
                        <button type="submit" data-id = "' . $ddj->getId() . '" class="form-control btn btn-warning" id="btn_edit_ddj" ><span>Enregistrer</span></button>
                    </div>
                </form>
            ';
            $data = json_encode($html);
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/checkout_h_res", name = "checkout_h_res")
     */
    public function checkout_h_res(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id = $request->get('ddj_id');
            $ddj = $repoDdj->find($id);
            $html = '';
            $html .= '
            
               <form action="">
                    <div class="form-group">
                        <label>Chiffre d\'affaire (Ariary) :
                        </label>
                        <input type="text" id = "modal_modif_res_ca" class="form-control ca" value="'. $ddj->getResCa() . '">
                    </div>
                    <div class="form-group">
                        <label>Petit déjeuner (Couverts) :
                        </label>
                        <input type="text" id = "modal_modif_res_p_dej"  class="form-control" value="'. $ddj->getResPDej() . '">
                    </div>
                    <div class="form-group">
                        <label>Déjeuner (Couverts) :
                        </label>
                        <input type="text" id = "modal_modif_res_dej"  class="form-control" value="' . $ddj->getResDej() . '">
                    </div>
                    <div class="form-group">
                        <label>Dinner (Couverts) :
                        </label>
                        <input type="text" class="form-control" id = "modal_modif_res_dinner"  value="' . $ddj->getResDinner() . '">
                    </div>
                    <div class="form-group">
                        <button type="submit" data-id = "' . $ddj->getId() . '" class="form-control btn btn-warning" id="btn_edit_ddj"><span>Enregistrer</span></button>
                    </div>
                </form>
            ';
            $data = json_encode($html);
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/checkout_h_spa", name = "checkout_h_spa")
     */
    public function checkout_h_spa(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $id = $request->get('ddj_id');
            $ddj = $repoDdj->find($id);
            $html = '';
            $html .= '
            
               <form>
                    <div class="form-group">
                        <label>Chiffre d\'affaire (Ariary) :
                        </label>
                        <input type="text" value="'. $ddj->getSpaCa() . '" id = "modal_modif_spa_ca" class="form-control ca">
                    </div>
                    <div class="form-group">
                        <label>Nombre d\'abonné (Abonnés) :
                        </label>
                        <input type="text" value="' . $ddj->getSpaNAbonne() . '" id = "modal_modif_spa_n_abonne" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Client unique (Clients) :
                        </label>
                        <input type="text" value="' . $ddj->getSpaCUnique() . '" id = "modal_modif_spa_c_unique"  class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="submit" id="btn_edit_ddj"  data-id = "' . $ddj->getId() . '" class="form-control btn btn-warning" ><span>Enregistrer</span></button>
                    </div>
                </form>
            ';
            //$html .= '<p>Fenitra kely</p>';
            $data = json_encode($html);
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }


    /**
     * @Route("/profile/historique/heb", name = "edit_historique_heb")
     */
    public function edit_historique_heb(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
       $response = new Response();
        if($request->isXmlHttpRequest()){
            $id = $request->get('id');
            $heb_to = $request->get('heb_to');
            $heb_ca = $request->get('heb_ca');
            $created_at = date_create($request->get('created_at'));
            $ddj = $repoDdj->find($id);
            $ddj->setHebCa($heb_ca);
            $ddj->setHebTo($heb_to);

            $manager->flush();



            $data = json_encode("ok");
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/historique/res", name = "edit_historique_res")
     */
    public function edit_historique_res(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            $res_ca = $request->get('res_ca');
            $res_p_dej = $request->get('res_p_dej');
            $res_dej = $request->get('res_dej');
            $res_dinner = $request->get('res_dinner');
            $ddj = $repoDdj->find($id);
           $ddj->setResCa($res_ca);
           $ddj->setResPDej($res_p_dej);
           $ddj->setResDej($res_dej);
           $ddj->setResDinner($res_dinner);

            $manager->flush();



            $data = json_encode("ok");
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/profile/historique/spa", name = "edit_historique_spa")
     */
    public function edit_historique_spa(Request $request, ClientRepository $repoClient, HotelRepository $repoHotel, EntityManagerInterface $manager, DonneeDuJourRepository $repoDdj)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');
            $spa_ca = $request->get('spa_ca');
            $spa_n_abonne = $request->get('spa_n_abonne');
            $spa_c_unique = $request->get('spa_c_unique');
            
            $ddj = $repoDdj->find($id);
            $ddj->setSpaCa($spa_ca);
            $ddj->setSpaNAbonne($spa_n_abonne);
            $ddj->setSpaCUnique($spa_c_unique);
            $manager->flush();



            $data = json_encode("ok");
            $response->headers->set('Content-type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
}
