<?php

require BASE . DS . 'handlers/media.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

$app->group('/media', function (App $app) {

    $app->map(['GET'], '/{mediaId:[0-9]+}[/]', function (Request $request, Response $response, $args) {
        $mediaId = $args['mediaId'];
        $media = Mediahandler::getInstance()->getItem($mediaId);
        return $response->withJson($media, 200, JSON_UNESCAPED_UNICODE);
    })->setName('media-item');

});