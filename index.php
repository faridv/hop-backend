<?php

define('BASE', dirname(__FILE__));

require 'vendor/slim/autoload.php';
require 'vendor/predis/autoload.php';

require 'helpers/defines.php';


$config = [
    'settings' => [
        'displayErrorDetails' => true
    ],
];

$app = new \Slim\App($config);

require 'routes/routes.php';

$app->run();
