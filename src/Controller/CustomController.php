<?php

namespace App\Controller;

use App\Entity\Visit;
use App\Entity\Customer;
use App\Repository\VisitRepository;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomController extends AbstractController
{
    /**
     * @Route("/profile/custom/store", name="custom_store")
     */
    public function custom_store(ClientRepository $repoclient, 
    EntityManagerInterface $manager,
    CustomerRepository $repoCustomer,
    VisitRepository $repoVisit
    ): Response
    {
        $clients = $repoclient->findAll();
        $customers = $repoCustomer->findall();
        // insertion clients in customer and visit tables

        foreach($clients as $client){
            $name = $client->getNom();
            $lastName  = $client->getPrenom();
            $customerOfIt = $repoCustomer->findOneBy([
                "name"      => $name,
                "lastName"  => $lastName
            ]);
            if(!$customerOfIt){
                // add it to a new customer and after to visit
                $customer = new Customer();
                $customer->setName($name);
                $customer->setLastName($lastName);
                $customer->setEmail($client->getEmail());
                $customer->setTelephone($client->getTelephone());
                $customer->setCreatedAt($client->getCreatedAt());

                // new visitn for this customer
                $manager->persist($customer);
                $visit = new Visit();

                $visit->setCustomer($customer);
                $visit->setCheckin($client->getDateArrivee());
                $visit->setCheckout($client->getDateDepart());
                $visit->setProvenance($client->getProvenance());
                $visit->setSource($client->getSource());
                $visit->setMontant($client->getPrixTotal());
                $visit->setHotel($client->getHotel());
                $visit->setNbrNuitee($client->getDureeSejour());
                $visit->setNbrChambre($client->getNbrChambre());
                $visit->setCreatedAt($client->getDateArrivee());
                $manager->persist($visit);

                $manager->flush();
                
            }
            else if($customerOfIt){
                // just add it into visit data
                $visit = new Visit();

                $visit->setCustomer($customerOfIt);
                $visit->setCheckin($client->getDateArrivee());
                $visit->setCheckout($client->getDateDepart());
                $visit->setProvenance($client->getProvenance());
                $visit->setSource($client->getSource());
                $visit->setMontant($client->getPrixTotal());
                $visit->setHotel($client->getHotel());
                $visit->setNbrChambre($client->getNbrChambre());
                $visit->setNbrNuitee($client->getDureeSejour());
                $visit->setCreatedAt($client->getDateArrivee());
                $manager->persist($visit);

                $manager->flush();
            }
        }


        return new Response();
    }
}
