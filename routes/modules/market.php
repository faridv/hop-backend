<?php

require BASE . DS . 'handlers/market.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/market', function (App $app) {

    // market data
    $app->map(['GET'], '/data/{parentId:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $parentId = $args['parentId'];
        $items = MarketHandler::getInstance()->getData($parentId);
        return $response->withJson($items, 200, JSON_UNESCAPED_UNICODE);
    })->setName('market-data');

    // market labels
    $app->map(['GET'], '/labels/{parentId:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $parentId = $args['parentId'];
        $items = MarketHandler::getInstance()->getLabels($parentId);
        return $response->withJson($items, 200, JSON_UNESCAPED_UNICODE);
    })->setName('market-labels');

});