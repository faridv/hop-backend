<?php

require BASE . DS . 'handlers/news.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/news', function (App $app) {

    // all news
    $app->map(['GET'], '', function (Request $request, Response $response, $args) {
        $news = NewsHandler::getInstance()->getAll();
        return $response->withJson($news, 200, JSON_UNESCAPED_UNICODE);
    })->setName('news-all');

});