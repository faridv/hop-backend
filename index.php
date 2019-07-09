<?php

define('DS', DIRECTORY_SEPARATOR);
define('BASE', dirname(__FILE__));

require 'helpers/includes.php';

require 'vendor/vendor-loader.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ],
]);

require 'routes/routes.php';

$app->run();
