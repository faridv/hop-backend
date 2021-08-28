<?php

require BASE . DS . 'handlers/islamic-prayers.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/islamic-prayers', function (App $app) {

    // locations
    $app->map(['GET'], '/{locations:[0-9,-;]+}[/]', function (Request $request, Response $response, $args) {
        $locations = $args['locations'];
        $items = IslamicPrayers::getInstance()->getByLocationsList($locations);
        return $response->withJson($items, 200, JSON_UNESCAPED_UNICODE);
    })->setName('islamic-prayers');

});