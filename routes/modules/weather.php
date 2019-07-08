<?php

require BASE . DS . 'handlers/weather.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/weather', function (App $app) {

    $app->map(['GET'], '', function (Request $request, Response $response, $args) {
        $lon = null !== $request->getParam('lon') ? $request->getParam('lon') : '51.4231';
        $lat = null !== $request->getParam('lat') ? $request->getParam('lat') : '35.6961';
        $weather = WeatherHandler::getInstance()->get($lon, $lat);
        return $response->withJson($weather, 200, JSON_UNESCAPED_UNICODE);
    })->setName('weather');

});