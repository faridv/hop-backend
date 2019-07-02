<?php

use Slim\App as App;

$app->group('/news', function (App $app) {

    $app->map(['GET'], '', function ($request, $response, $args) {
        $response->getBody()->write('all news');
    })->setName('news-all');

    $app->get('/{id:[0-9]+}', function ($request, $response, $args) {
        // Route for /news/{id:[0-9]+}
        // Reset the password for user identified by $args['id']
        $response->getBody()->write('news details for item with id: ' . $args['id']);
    })->setName('user-password-reset');

    $app->post('/ticket/new', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $ticket_data = [];
        $ticket_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $ticket_data['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);
    });
});