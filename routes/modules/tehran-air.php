<?php

require BASE . DS . 'handlers/tehran-air.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/tehran-air', function (App $app) {

    // all news
    $app->map(['GET'], '/{type:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $type = $args['type'];
        $news = TehranAirHandler::getInstance()->get($type);
        return $response->withJson($news, 200, JSON_UNESCAPED_UNICODE);
    })->setName('tehran-air');

});