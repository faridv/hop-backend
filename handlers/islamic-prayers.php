<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use ResponseHelper as Response;

use IslamicNetwork\PrayerTimes\PrayerTimes;
use IslamicNetwork\PrayerTimes\DMath;
use IslamicNetwork\PrayerTimes\Method;

date_default_timezone_set('Asia/Tehran');

final class IslamicPrayers {

    private static $instance = null;
    private $config;
    private $today = '';

    private function __construct() {
        $this->config = Config::getInstance()->data->modules->{'islamic-prayers'};
        $this->today = date('Y-m-d', time());
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new IslamicPrayers();
        }
        return self::$instance;
    }

    public function getByLocationsList($locations) {
        $date = new DateTime(Date(DATE_RFC2822, strtotime('2021-08-10')), new DateTimezone('Asia/Tehran'));
        $coordinationList = explode(';', $locations);
        $output = new stdClass();
        foreach ($coordinationList as $coord) {
            list($latitude, $longitude) = explode(',', $coord);
            $pt = new PrayerTimes('TEHRAN');
            $times = $pt->getTimes($date, $latitude, $longitude,
                null,
                PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_NONE,
                PrayerTimes::MIDNIGHT_MODE_JAFARI,
                PrayerTimes::TIME_FORMAT_FLOAT);
            foreach ($times as $key => $time) {
                $times[$key] = self::convertTime(DMath::fixHour($time + 0.5 / 60));
            }
            $times['_date'] = JDate::gregorianToJalaali($date)->format('yyyy-MM-dd');
            $temp = (array)$this->separateAdditionalTimes($times);
            $new = (object)array_combine(array_map('strtolower', array_keys($temp)), $temp);
            $output->{$coord} = $new;
        }
        return $output;
    }

    private function separateAdditionalTimes($times) {
        $times['_asr'] = $times['Asr'];
        $times['_isha'] = $times['Isha'];
        $times['_imsak'] = $times['Imsak'];
        unset($times['Asr']);
        unset($times['Isha']);
        unset($times['Imsak']);
        return $times;
    }

    private function convertTime($dec) {
        $seconds = ($dec * 3600);
        // we're given hours, so let's get those the easy way
        $hours = floor($dec);
        // since we've "calculated" hours, let's remove them from the seconds variable
        $seconds -= $hours * 3600;
        // calculate minutes left
        $minutes = floor($seconds / 60);
        // remove those from seconds as well
        $seconds -= $minutes * 60;
        // return the time formatted HH:MM:SS
        return self::padZero($hours) . ":" . self::padZero($minutes) . ":" . self::padZero(explode('.', $seconds)[0]);
    }

    private function padZero($num) {
        return (strlen($num) < 2) ? "0{$num}" : $num;
    }

}