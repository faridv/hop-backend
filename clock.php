<?php

header('Content-Type: application/json');

$defaultTimezone = 'Asia/Tehran';
$timezone = isset($_GET['tz']) ? (string)$_GET['tz'] : $defaultTimezone;
$timezone = isValidTimezoneId($timezone) ? $timezone : $defaultTimezone;

function isValidTimezoneId($timezoneId) {
    $zoneList = timezone_identifiers_list(); # list of (all) valid timezones
    return in_array($timezoneId, $zoneList); # set result
}

$clock = new DateTime("now", new DateTimeZone($timezone));

$output = new stdClass();
$output->timezone = $timezone;
$output->clock = $clock->format('Y-m-d H:i:s');

echo json_encode($output);
die();