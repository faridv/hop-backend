<?php

require BASE . DS . 'handlers/uhd.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/items', function (App $app) {

    $app->map(['GET'], '[/{uri:.*}]', function (Request $request, Response $response, $args) {
        $items = UhdHandler::getInstance()->getAll($request, $args);
        return $response->withJson($items, 200, JSON_UNESCAPED_UNICODE);
    })->setName('items');

});