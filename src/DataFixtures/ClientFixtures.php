<?php

namespace App\DataFixtures;

use DateInterval;
use App\Entity\User;
use App\Entity\Hotel;
use App\Entity\Client;
use App\Entity\DonneeDuJour;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Migrations\Version\Factory;
use App\Repository\DonneeDuJourRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientFixtures extends Fixture
{
    private $repoClient;
    private $repoHotel;
    private $repoDdj;
    private $passwordEncoder;
    public function __construct(ClientRepository $repoClient, HotelRepository $repoHotel, DonneeDuJourRepository $repoDdj, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->repoClient = $repoClient;
        $this->repoHotel = $repoHotel;
        $this->repoDdj = $repoDdj;
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 1; $i <= 4; $i++) {
           $hotel = new Hotel();
           if($i == 1){
               $hotel->setNom("Royal Beach");
               $hotel->setPseudo('royal_beach');
               $hotel->setLieu('Nosy Be');
                $manager->persist($hotel);
                $manager->flush();
           }
            if ($i == 2) {
                $hotel->setNom("Calypso");
                $hotel->setPseudo('calypso');
                $hotel->setLieu('Tamatave');
                $manager->persist($hotel);
                $manager->flush();
            }
            if ($i == 3) {
                $hotel->setNom("Baobab Tree");
                $hotel->setPseudo('baobab_tree');
                $hotel->setLieu('Majunga');
                $manager->persist($hotel);
                $manager->flush();
            }
            if ($i == 4) {
                $hotel->setNom("Vanila Hotel");
                $hotel->setPseudo('vanila_hotel');
                $hotel->setLieu('Nosy Be');
                $manager->persist($hotel);
                $manager->flush();
            }
        }


        $hotels = $this->repoHotel->findAll();

        foreach($hotels as $h){
            for($i = 1; $i<=30; $i++){
                $client = new Client();
                $client->setNom($faker->name);
                $client->setPrenom($faker->lastName);
                $today = new \DateTime();
               
                $client->setDateArrivee($faker->dateTimeBetween('-5 years'));
                $date = date_create($client->getDateArrivee()->format("Y-m-d"));
                date_add($date, date_interval_create_from_date_string('-5 months'));
                $client->setCreatedAt($date);
                $client->setDureeSejour(rand(2,10));
                $date2 = date_create($client->getDateArrivee()->format("Y-m-d"));
                date_add($date2, date_interval_create_from_date_string($client->getDureeSejour() . ' days'));
                $client->setDateDepart($date2);
                $client->setHotel($h);
                $manager->persist($client);
                $manager->flush();
               
            }
        }
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setNom($faker->name);
            $user->setPrenom($faker->lastName);
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordEncoder->encodePassword($user, "password"));
            $user->setUsername("username");
            $user->setHotel("tous");
            $manager->persist($user);
            $manager->flush();
           
        }

        foreach ($hotels as $h) {
            for ($i = 1; $i <= 40; $i++) {
                $ddj = new DonneeDuJour();
                $ddj->setCreatedAt($faker->dateTimeBetween('-5 years'));
                $ddj->setHebTo(rand(10, 99));
                $ddj->setHebCa(rand(1000000 , 20000000));
                $ddj->setResCa(rand(1000000, 20000000));
                $ddj->setResNCouvert(rand(25, 100));
                $ddj->setResPDej(rand(25, 100));
                $ddj->setResDej(rand(25, 100));
                $ddj->setResDinner(rand(25, 100));
                $ddj->setSpaCa(rand(1000000, 20000000));
                $ddj->setSpaCUnique(rand(10, 100));
                $ddj->setSpaNAbonne(rand(20, 150));
                $ddj->setCrjDirection($faker->sentence());
                $ddj->setCrjServiceRh($faker->sentence());
                $ddj->setCrjCommercial($faker->sentence());
                $ddj->setCrjComptable($faker->sentence());
                $ddj->setCrjReception($faker->sentence());
                $ddj->setCrjSpa($faker->sentence());
                $ddj->setCrjRestaurant($faker->sentence());
                $ddj->setCrjSTechnique($faker->sentence());
                $ddj->setCrjLitiges($faker->sentence());
                $ddj->setHotel($h);
                $manager->persist($ddj);
                $manager->flush();
            }
        }

       
    }
}
