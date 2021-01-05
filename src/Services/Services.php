<?php

    namespace App\Services;

use App\Entity\User;
use App\Entity\DonneeMensuelle;
use App\Repository\HotelRepository;

class Services
    {
        public function all_date_between2_dates(\DateTime $date1, \DateTime $date2)
        {
            // $date1 = new \DateTime('2020-05-19');
            //  $date2 = new \DateTime('2020-05-25');
           
            $diff_days = 0;
            $tab = [];
            if ($date1 < $date2) {
                $diff_days = $date1->diff($date2)->days;
                //date_add($date1, date_interval_create_from_date_string(0 . 'days'));
                for ($i = 0; $i <= $diff_days; $i++) {
                    $d = date('Y-m-d', strtotime($date1->format("Y-m-d") . ' + ' . $i . ' days'));
                    array_push($tab, $d);
                    //dd(gettype($d)); => string
                }
            } else if ($date1 > $date2) {
                $diff_days = $date2->diff($date1)->days;
                for ($i = 0; $i <= $diff_days; $i++) {
                    $d = date('Y-m-d', strtotime($date2->format("Y-m-d") . ' + ' . $i . ' days'));
                    array_push($tab, $d);
                   
                }
            }
           
            return $tab;
        }

        public function tester_droit($pseudo_hotel, User $user, HotelRepository $repoHotel)
        {
            $son_hotel = $user->getHotel();
            if($son_hotel == 'tous'){
                return 'possible';
            }
            else{
                // on compare son hotel à celle mentionné
                $l_hotel = $repoHotel->findOneByNom($son_hotel);
                $le_pseudo = $l_hotel->getPseudo();
                if($pseudo_hotel == $le_pseudo){
                    return 'possible';
                }
                else{
                    return 'impossible';
                }
            }
        }

        public function parseMyDate($format) 
        {   
            if($format != ""){
                $tab = explode("/", $format);
                // si on est en 2100 on devra faire un mis à jour ici pour la config de l'année
                if (count($tab) > 2) {
                    $annee = end($tab);
                    $nb_chiffre = intval(log10(ceil($annee)) + 1);
                    if ($nb_chiffre == 2) {
                        $annee = 2000 + $annee;
                        //dd($tab[0]."-".$tab[1]."-".$annee);         
                        return $tab[0] . "-" . $tab[1] . "-" . $annee;
                    } else if ($nb_chiffre == 4) {
                        return $tab[1] . "-" . $tab[0] . "-" . $annee;
                    } else {
                        return "erreur";
                    }
                } else {
                    return "erreur";
                }  
            }
            else{
                return null;
            }
        }
        public function to_money($amount):string
        {
             $amount = floatval($amount);
        // $formatter = new \NumberFormatter('en_GB',  \NumberFormatter::CURRENCY);
        // //echo 'UK: ', $formatter->formatCurrency($amount, 'EUR'), PHP_EOL;
        // $x = $formatter->formatCurrency($amount, 'USD');

        // $y = str_replace("US$", " ", $x);
        // $z = str_replace(",", " ", $y);
        $y = number_format($amount, 2);
        $z = str_replace(",", " ", $y);
            
            return $z;
        }

        public function no_space($value)
        {
            $x = str_replace(" ", "", $value);
            //dd($x);
            return doubleval($x);
        }
        public function tab_annee() :array
        {
            $today = new \Datetime();
            $annee = intVal($today->format('Y'));
            $prec = $annee--;
            $tab_annee = [$annee, $prec];
            return $tab_annee;
        }
        
    } 