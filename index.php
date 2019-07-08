<?php

define('DS', DIRECTORY_SEPARATOR);
define('BASE', dirname(__FILE__));

require 'helpers/includes.php';

require 'vendor/slim/autoload.php';
require 'vendor/predis/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ],
]);

require 'routes/routes.php';

$app->run();
