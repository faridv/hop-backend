<?php

require BASE . DS . 'handlers/ifilm.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/ifilm', function (App $app) {

    $app->map(['GET'], '[/]', function (Request $request, Response $response, $args) {
        $home = IfilmHandler::getInstance()->getHomepage();
        return $response->withJson($home, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-home');


    $app->map(['GET'], '/news[/]', function (Request $request, Response $response, $args) {
        $news = IfilmHandler::getInstance()->getNews();
        return $response->withJson($news, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-news');


    $app->map(['GET'], '/news/{id:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $newsId = $args['id'];
        $item = IfilmHandler::getInstance()->getNewsDetail($newsId);
        return $response->withJson($item, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-news-detail');


    // $app->map(['GET'], '/schedule/{date:[0-9A-Za-z\:\-]+}', function (Request $request, Response $response, $args) {
    $app->map(['GET'], '/schedule[/]', function (Request $request, Response $response, $args) {
        // $date = isset($args['date']) ? $args['date'] : null;
		$date = null !== $request->getParam('date') ? $request->getParam('date') : Date('Y-m-d', time());
        $channels = IfilmHandler::getInstance()->getSchedule($date);
        return $response->withJson($channels, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-schedule');


    $app->map(['GET'], '/series[/]', function (Request $request, Response $response, $args) {
        $series = IfilmHandler::getInstance()->getSeries();
        return $response->withJson($series, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-series');


    $app->map(['GET'], '/series/{id:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $serieId = $args['id'];
        $item = IfilmHandler::getInstance()->getSerieEpisodes($serieId);
        return $response->withJson($item, 200, JSON_UNESCAPED_UNICODE);
    })->setName('ifilm-series-detail');

});