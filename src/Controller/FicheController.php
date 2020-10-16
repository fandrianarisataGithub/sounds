<?php

namespace App\Controller;

use App\Repository\HotelRepository;
use App\Repository\FicheHotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FicheController extends AbstractController
{
    /**
     * @Route("/profile/modif_fiche/{pseudo_hotel}", name = "modif_fiche")
     */
    public function modif_fiche($pseudo_hotel, Request $request, FicheHotelRepository $repoFiche, EntityManagerInterface $manager, HotelRepository $repoHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $option = $request->get('option');
            if($option == "chambre"){

                $cp = $request->get('cp');
                $sf = $request->get('sf');
                $cd = $request->get('cd');
                $sv = $request->get('sv');

                $fiches = $repoFiche->findAll();
                $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $id = $hotel->getId();
                foreach ($fiches as $fiche) {
                    $son_hotel = $fiche->getHotel();
                    $son_id_hotel = $son_hotel->getId();
                    if ($id == $son_id_hotel) {
                        $fiche->setCPrestige($cp);
                        $fiche->setSFamilliale($sf);
                        $fiche->setCDeluxe($cd);
                        $fiche->setSVip($sv);
                        
                        $manager->flush();
                    }
                }

                $data = json_encode("ok");
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;

            }
            if ($option == "no-chambre") {

                $cp = $request->get('cp');
                $sf = $request->get('sf');
                $cd = $request->get('cd');
                $sv = $request->get('sv');

                $fiches = $repoFiche->findAll();
                $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $id = $hotel->getId();
                foreach ($fiches as $fiche) {
                    $son_hotel = $fiche->getHotel();
                    $son_id_hotel = $son_hotel->getId();
                    if ($id == $son_id_hotel) {
                        $ln = $request->get('ln');
                        $sview = $request->get('sview');
                        $fiche->setLeNautile($ln);
                        $fiche->setSunsetView($sview);
                        $manager->flush();
                    }
                }

                $data = json_encode("ok");
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }

        }

        $fiches = $repoFiche->findAll();
        $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
        $id = $hotel->getId();
        foreach($fiches as $fiche){
            $son_hotel = $fiche->getHotel();
           $son_id_hotel = $son_hotel->getId();
           if($id == $son_id_hotel){
               dd('eo');
           }
        }

        return $this->render('fiche/index.html.twig');
    }
    /**
     * @Route("/profile/affiche_fiche/{pseudo_hotel}", name = "load_fiche")
     */
    public function load_fiche($pseudo_hotel, Request $request, FicheHotelRepository $repoFiche, EntityManagerInterface $manager, HotelRepository $repoHotel)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {

            $fiches = $repoFiche->findAll();
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $id = $hotel->getId();
            foreach ($fiches as $fiche) {
                $son_hotel = $fiche->getHotel();
                $son_id_hotel = $son_hotel->getId();
                if ($id == $son_id_hotel) {
                    $cp = $fiche->getCPrestige();
                    $sf = $fiche->getSFamilliale();
                    $cd = $fiche->getCDeluxe();
                    $sv = $fiche->getSVip();
                    $ln = $fiche->getLeNautile();
                    $sview = $fiche->getSunsetView();

                    $t = 0;
                    $t += $cp;
                    $t += $sf;
                    $t += $cd;
                    $t += $sv;
                    
                    $html = '';
                    $html .= '
                        <section class="n_form">
							<div class="h4_titre">
								<h4>Chambres</h4>
								<button class="btn btn-default" data-toggle="modal" data-target="#modal_form_fh_1">
									<span class="fa fa-plus"></span>
									<span class="text">Modifier</span>
								</button>
							</div>
							<div class="data_form">
								<div class="form-group">
									<span class="val_data" id = "scp"> '. $cp .' </span>
									<label>Chambres Préstiges</label>
								</div>
								<div class="form-group">
									<span class="val_data" id = "ssf">'. $sf .'</span>
									<label>Suites Familiales</label>
								</div>
							</div>
							<div class="data_form">
								<div class="form-group">
									<span class="val_data" id = "scd">'. $cd .'</span>
									<label>Chambres Deluxes</label>
								</div>
								<div class="form-group">
									<span class="val_data" id = "ssv">'. $sv .'</span>
									<label>Suites VIP</label>
								</div>
							</div>
							<div class="data_form">
								<div class="form-group total_chambre">
									<label>Nombre total des chambres :
									</label>
									<span class="val_data">
										'. $t .'
									</span>
									<span>Chambres</span>
								</div>

							</div>
						</section>
						<section class="n_form">
							<div class="h4_titre">
								<h4>Restaurant</h4>
								<button class="btn btn-default" data-toggle="modal" data-target="#modal_form_fh_2">
									<span class="fa fa-plus"></span>
									<span class="text">Modifier</span>
								</button>
							</div>
							<div class="data_form">
								<div class="form-group">
									<label class="label_first">Le Nautile</label>
									<span class="val_data" id="sln">' . $ln . '</span>
									<span class="span__ar">Couverts Maximum</span>
								</div>
							</div>
							<div class="data_form">
								<div class="form-group">
									<label class="label_first">Sunset View
									</label>
									<span class="val_data" id = "ssview">'. $sview .'</span>
									<span class="span__ar">Couvert Maximum</span>
								</div>
							</div>

						</section>
                    ';
                }
            }

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
            
        }

    }
}
