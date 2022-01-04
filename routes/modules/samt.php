<?php

require BASE . DS . 'handlers/samt.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/samt', function (App $app) {

    // market data
    $app->map(['GET'], '/{query}[/]', function (Request $request, Response $response, $args) {
        $query = $args['query'];
        $channelId = $request->getParam('channelid');
        $items = SamtHandler::getInstance()->getData($query, $channelId);
        return $response->withJson($items, 200, JSON_UNESCAPED_UNICODE);
    })->setName('samt-item');

});