<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;

$app->get('/', function (Request $request, Response $response, array $args) {
    $response = new \Slim\Http\Response(200);
    $response->write('{"success": true, "message": "HbbTV Backend"}');
    return $response;
});

$c = new \Slim\Container(); //Create Your container
//Override the default Not Found Handler after App
unset($app->getContainer()['notFoundHandler']);
$app->getContainer()['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $response = new \Slim\Http\Response(404);
        return $response->write('{"success":false,"message":"endpoint not found."}');
    };
};