<?php

require BASE . DS . 'handlers/quran.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/quran', function (App $app) {

    $app->map(['GET'], '', function (Request $request, Response $response, $args) {
        return $response->write('{"success":true,"message":"Welcome to Quran API"}');
    })->setName('quran');

    $app->map(['GET'], '/surah[/]', function (Request $request, Response $response, $args) {
        $surahList = QuranHandler::getInstance()->getSurahList();
        return $response->withJson($surahList, 200, JSON_UNESCAPED_UNICODE);
    })->setName('quran-surah-list');

    $app->map(['GET'], '/surah/{id:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $surah = QuranHandler::getInstance()->getSurah($args['id']);
        return $response->withJson($surah, 200, JSON_UNESCAPED_UNICODE);
    })->setName('quran-surah');

});