<?php

// timezone for my country Europe/Tirane
date_default_timezone_set('Europe/Tirane');

class DateTimeLocal
{
    public function format_str(string $format_structure)
    {
        $date_now = new DateTime('now');
        $formatted_date = $date_now->format($format_structure);

        return $formatted_date;
    }

    public function set_expire_time(int $expected_time_to_expire = null)
    {
        if ($expected_time_to_expire === null) $expected_time_to_expire = 1 || exit('Time cannot be blank/empty/null!');
        if ($expected_time_to_expire <= 0) die('No need for this-> 0.');


        $time_now = time();
        $seconds =  $time_now;

        $time_to_expire = time() + ($expected_time_to_expire * 60);


        $correct_date = new DateTime(date("Y-m-d H:i:s", $seconds));
        $expected_expired_date = new DateTime(date("Y-m-d H:i:s", $time_to_expire));



        $expired_time = $correct_date->diff($expected_expired_date);


        $expired_msg = '';

        if ($expired_time->format('%h') > 0)
            $expired_msg .= $expired_time->format('%h') . ($expired_time->format('%h') < 10 ? " hour " :  " hours ");

        if ($expired_time->format('%i') > 0)
            $expired_msg .= $expired_time->format('%i') . ($expired_time->format('%i') < 10 ? " minute " :  " minutes ");

        if ($expired_time->format('%s') > 0)
            $expired_msg .= $expired_time->format('%s') . ($expired_time->format('%s') < 10 ? " second " :  " seconds ");


        return [
            "time_to_str" => $expired_msg,
            "time" => $time_to_expire
        ];
    }
}


$datetime = new DateTimeLocal();
