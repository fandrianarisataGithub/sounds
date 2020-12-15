<?php

namespace App\Controller;

use App\Services\Services;
use App\Entity\DonneeMensuelle;
use App\Form\DonneeMensuelleType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DonneeMensuelleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class DonneeMensuelleController extends AbstractController
{

    /**
     * @Route("/profile/donnee_mensuelle/{pseudo_hotel}", name="donnee_mensuelle")
     */
    public function donnee_mensuelle(Services $services, Request $request, $pseudo_hotel, EntityManagerInterface $manager, SessionInterface $session, HotelRepository $reposHotel)
    {
        $today = new \DateTime();
        $donnee_mensuelle = new DonneeMensuelle();
        $form = $this->createForm(DonneeMensuelleType::class, $donnee_mensuelle);
        $data_session = $session->get('hotel');
        $data_session['current_page'] = "donnee_mensuelle";
        $data_session['pseudo_hotel'] = $pseudo_hotel;
        $user = $data_session['user'];
        $hotel = $reposHotel->findBy(['pseudo' => $pseudo_hotel]);
        // dd($hotel);
        $pos = $services->tester_droit($pseudo_hotel, $user, $reposHotel);
        $form->handleRequest($request);
        if ($pos == "impossible") {
            return $this->render('/page/error.html.twig');
        } else {
            // traitemenet des données
            if ($form->isSubmitted() && $form->isValid()) {
                $donnee_mensuelle = $form->getData();
                // dd($donnee_mensuelle);
                $mois = $request->request->get('donnee_mensuelle_mois');
                $annee = $request->request->get('donnee_mensuelle_annee');
                $s = $mois . "-" . $annee;
                $repoDM = $this->getDoctrine()->getRepository(DonneeMensuelle::class);
                $un_repo_mois_hotel = $repoDM->findBy(['mois' => $s, 'hotel' => $hotel[0]]);
                if ($un_repo_mois_hotel) {
                    // on l'efface pour garder la derniere
                    $manager->remove($un_repo_mois_hotel[0]);
                    $manager->flush();
                    $donnee_mensuelle->setMois($s);
                    $donnee_mensuelle->setHotel($hotel[0]);
                    // dd($donnee_mensuelle);
                    $manager->persist($donnee_mensuelle);
                    $manager->flush();
                }
                if (!$un_repo_mois_hotel) {
                    $donnee_mensuelle->setMois($s);
                    $donnee_mensuelle->setHotel($hotel[0]);
                    // dd($donnee_mensuelle);
                    $manager->persist($donnee_mensuelle);
                    $manager->flush();
                }
            }
            return $this->render('donnee_mensuelle/donnee_mensuelle.html.twig', [
                "id"            => "li__donnee_mensuelle",
                "hotel"         => $data_session['pseudo_hotel'],
                "current_page"  => $data_session['current_page'],
                "today"         => 2,
                'form'          => $form->createView(),
            ]);
        }
    }
    /**
     * @Route("/profile/tester_mois_dm/{pseudo_hotel}", name="tester_mois_dm")
     */
    public function tester_mois_dm($pseudo_hotel, Request $request, HotelRepository $repoHotel, DonneeMensuelleRepository $repoDM)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $mois = $request->get('mois');
            $hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
            $exist = $repoDM->findBy(['hotel'=>$hotel, 'mois'=>$mois]);
            $data = json_encode("vide");
            if($exist){
                $data = json_encode("non vide");
            }
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
}
