<?php

    namespace App\Services;

use App\Entity\User;
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
    } 