<?php

require(__DIR__ . '/../vendor/jdatetime.php');

use \farhadi\IntlDateTime;

abstract class JDate {

    public static function jalaaliToGregorian($time = null, $timezone = 'Asia/Tehran') {
        return new IntlDateTime($time, $timezone, 'gregorian', 'en', null);
    }

    public static function gregorianToJalaali($time = null, $timezone = 'Asia/Tehran') {
        return new IntlDateTime($time, $timezone, 'persian', 'en', null);
    }

    public static function convert($time = null, $timezone = null, $calendar = 'persian', $locale = 'en', $pattern = null) {
        return new IntlDateTime($time, $timezone, $calendar, $locale, $pattern);
    }

}