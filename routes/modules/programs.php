<?php

require BASE . DS . 'handlers/programs.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/programs', function (App $app) {

    // Get programs list
    $app->map(['GET'], '[/]', function (Request $request, Response $response, $args) {
        $programs = ProgramHandler::getInstance()->get();
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('programs');

    // Get programs list [2]
    $app->map(['GET'], '/latest[/]', function (Request $request, Response $response, $args) {
        $programs = ProgramHandler::getInstance()->getLatest();
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('programs-list');

    // Get programs by type [2]
    $app->map(['GET'], '/type/{type:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $programType = $args['type'];
        $programs = ProgramHandler::getInstance()->getList($programType);
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('programs-list');

    // Get program episodes by program id
    $app->map(['GET'], '/{programId:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $programId = $args['programId'];
        $programs = ProgramHandler::getInstance()->getEpisodes($programId);
        return $response->withJson($programs, 200, JSON_UNESCAPED_UNICODE);
    })->setName('program-episodes');

});