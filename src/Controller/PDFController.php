<?php

namespace App\Controller;


use Dompdf\Dompdf;
use Dompdf\Options;
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
    public function pdf_ddj(Request $request, DonneeDuJourRepository $repoDdj, HotelRepository $repoHotel, ClientRepository $repoClient): Response
    {
           
            if($request->request->count() > 0){
             
                $date = $request->request->get('date');
                $hotel = $request->request->get('hotel');
                $hotel = $repoHotel->findOneBy(["pseudo" => $hotel]);
                $date = date_create($date);
                $ddj = $repoDdj->findOneBy(["createdAt" => $date, "hotel" => $hotel]);
                $clients = $repoClient->findBy(["createdAt" => $date, "hotel" => $hotel]);
            
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
                $dompdf->stream("donnee_du_jour_".$hotel->getPseudo()."_".$date->format("d-m-Y"), [
                    "Attachment" => true
                ]);
            }
            return new Response("le PDF a été téléchargé");
           
    }
}
