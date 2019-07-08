<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json; charset=utf-8');
header('Acess-Control-Allow-Credentials: true');

require_once BASE . DS . 'config/default.php';
require_once BASE . DS . 'helpers/proxy.php';

//$config = new Config();