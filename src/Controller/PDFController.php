<?php

namespace App\Controller;


use Dompdf\Dompdf;
use Dompdf\Options;
use App\Services\Services;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DonneeDuJourRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class PDFController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/profile/pdf_ddj", name ="pdf_ddj")
     */
    public function pdf_ddj(Services $services ,Request $request, DonneeDuJourRepository $repoDdj, HotelRepository $repoHotel, ClientRepository $repoClient): Response
    {
           
            if($request->request->count() > 0){
             
                $date = $request->request->get('date');
                $pseudo_hotel = $request->request->get('hotel');
                $hotel = $repoHotel->findOneBy(["pseudo" => $pseudo_hotel]);
                $date = date_create($date);
                $ddj = $repoDdj->findOneBy(["createdAt" => $date, "hotel" => $hotel]);
            

                //$all_date_asked = $services->all_date_between2_dates($date1, $date2);
                $all_date_asked = [$date->format('Y-m-d')];
                //dd($all_date_asked);
                
                $tab = [];
               
                $data_session['current_page'] = "crj";
                $data_session['pseudo_hotel'] = $pseudo_hotel;
                $l_hotel = $repoHotel->findOneByPseudo($pseudo_hotel);
                $current_id_hotel = $l_hotel->getId();
                $clients = $repoClient->findAll();
                foreach ($clients as $item) {
                    $son_id_hotel = $item->getHotel()->getId();
                    if ($son_id_hotel == $current_id_hotel) {
                        array_push($tab, $item);
                    }
                }
                $clients = [];
                foreach ($tab as $client) {
                    // on liste tous les jour entre sa dete arrivee et date depart
                    $sa_da = $client->getDateArrivee();
                    $sa_dd = $client->getDateDepart();
                    //dd($sa_dd);
                    $his_al_dates = $services->all_date_between2_dates($sa_da, $sa_dd);
                    //dd($his_al_dates);
                    for ($i = 0; $i < count($all_date_asked); $i++) {
                        for ($j = 0; $j < count($his_al_dates); $j++) {
                            if ($all_date_asked[$i] == $his_al_dates[$j]) {
                                if (!in_array($client, $clients)) {
                                    array_push($clients, $client);
                                }
                            }
                        }
                    }
                }
            
                // Configure Dompdf according to your needs
                $pdfOptions = new Options();
                $pdfOptions->set('defaultFont', 'Arial');

                // Instantiate Dompdf with our options
                $dompdf = new Dompdf($pdfOptions);
                
                // Retrieve the HTML generated in our twig file
                $html = $this->renderView('pdf/donnee_du_jour_pdf.html.twig', [
                    "ddj"       => $ddj,
                    "clients"   => $clients,
                    "date"      => $date
                ]);

                // Load HTML to Dompdf
                $dompdf->loadHtml($html);

                // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
                $dompdf->setPaper('A4', 'portrait');

                // Render the HTML as PDF
                $dompdf->render();
               
                // Output the generated PDF to Browser (inline view)
                
                $dompdf->stream("donnee_du_jour_" . $pseudo_hotel . "_" . $date->format('d-m-Y').".pdf", [
                    "Attachment" => true
                ]);
                
                exit();
            }
            return new Response("le PDF a été téléchargé");
           
    }
}
