<?php

require BASE . DS . 'handlers/sepehr.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/sepehr', function (App $app) {

    $app->map(['GET'], '/categories[/]', function (Request $request, Response $response, $args) {
        $channels = SepehrHandler::getInstance()->getCategories();
        return $response->withJson($channels, 200, JSON_UNESCAPED_UNICODE);
    })->setName('sepehr-categories');

    $app->map(['GET'], '/channels[/]', function (Request $request, Response $response, $args) {
        $channels = SepehrHandler::getInstance()->getChannels();
        return $response->withJson($channels, 200, JSON_UNESCAPED_UNICODE);
    })->setName('sepehr-channels-list');

    $app->map(['GET'], '/channels/{catid:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $channels = SepehrHandler::getInstance()->getChannelsByCategory($args['catid']);
        return $response->withJson($channels, 200, JSON_UNESCAPED_UNICODE);
    })->setName('sepehr-channels');

    $app->map(['GET'], '/epg/{channelid:[0-9]+}[/date/{date:[0-9A-Za-z\:\-]+}]', function (Request $request, Response $response, $args) {
        $channelId = isset($args['channelid']) ? $args['channelid'] : null;
        $date = isset($args['date']) ? $args['date'] : null;
        $epg = SepehrHandler::getInstance()->getEpg($channelId, $date);
        return $response->withJson($epg, 200, JSON_UNESCAPED_UNICODE);
    })->setName('sepehr-epg');

});