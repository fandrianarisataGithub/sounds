<?php

    namespace App\Services;
    
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
    } 