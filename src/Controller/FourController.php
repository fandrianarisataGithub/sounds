<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\Fournisseur;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FournisseurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FourController extends AbstractController
{
    /**
     * @Route("/profile/historique_fournisseur", name="historique_fournisseur")
     */
    public function historique_fournisseur(Services $services, Request $request, HotelRepository $repoHotel, EntityManagerInterface $manager, FournisseurRepository $repoFour)
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
                $fours = $repoFour->findAll();
                $tab = [];
                foreach ($fours as $item) {
                    $son_id_hotel = $item->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab, $item);
                    }
                }
                $tab_aff = [];
                $t = [];
                foreach ($tab as $item) {
                    // on liste tous les jour entre sa date arrivee et date depart
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

                    array_push($t, ['<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getType() . '</div>', '<div>' . $item->getNomFournisseur() . '</div>', '<div>' . $item->getMontant() . '</div>', '<div>' . $item->getEcheance() . '</div>', '<div>' . $item->getModePmt() . '</div>', '<div>' . $item->getMontantPaye() . '</div>', '<div>' . $item->getDatePmt()->format('d-m-Y') . '</div>', '<div>' . $item->getRemarque() . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            } else {
                $pseudo_hotel = $request->get('pseudo_hotel');
                $four = new Fournisseur();
                $current_id_hotel = $repoHotel->findOneByPseudo($pseudo_hotel)->getId();
                //dd($current_id_hotel);
                $fours = $repoFour->findAll();
                // dd($all_ddj);
                $tab_ddj = [];
                $t = [];
                foreach ($fours as $d) {
                    $son_id_hotel = $d->getHotel()->getId();
                    //dd($son_id_hotel);
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab_ddj, $d);
                    }
                }
                //    dd($tab_ddj);
                foreach ($tab_ddj as $item) {

                    array_push($t, ['<div>' . $item->getCreatedAt()->format('d-m-Y') . '</div>', '<div>' . $item->getNumeroFacture() . '</div>', '<div>' . $item->getType() . '</div>', '<div>' . $item->getNomFournisseur() . '</div>', '<div>' . $item->getMontant() . '</div>', '<div>' . $item->getEcheance() . '</div>', '<div>' . $item->getModePmt() . '</div>', '<div>' . $item->getMontantPaye() . '</div>', '<div>' . $item->getDatePmt()->format('d-m-Y') . '</div>', '<div>' . $item->getRemarque() . '</div>', '<div class="text-start"><a href="#" data-toggle="modal" data-target="#modal_formdisoana" data-id = "' . $item->getId() . '" class="btn btn_ddj_modif btn-primary btn-xs"><span class="fa fa-edit"></span></a></div>']);
                }

                $data = json_encode($t);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }
}
