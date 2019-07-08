<?php

require BASE . DS . 'handlers/schedule.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/schedule', function (App $app) {

    $app->map(['GET'], '', function (Request $request, Response $response, $args) {
        $date = null !== $request->getParam('date') ? $request->getParam('date') : Date('Y-m-d', time());
        $schedule = ScheduleHandler::getInstance()->get($date);
        return $response->withJson($schedule, 200, JSON_UNESCAPED_UNICODE);
    })->setName('schedule');

});