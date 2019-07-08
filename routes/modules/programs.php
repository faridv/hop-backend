<?php

require BASE . DS . 'handlers/programs.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/programs', function (App $app) {

    $app->map(['GET'], '', function (Request $request, Response $response, $args) {
        $programs = ProgramHandler::getInstance()->get();
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('programs');

    $app->map(['GET'], '/list', function (Request $request, Response $response, $args) {
        $programs = ProgramHandler::getInstance()->getList();
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('programs-list');

});