<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App as App;

require 'vendor/slim/autoload.php';


$config = [
    'settings' => [
        'displayErrorDetails' => true
    ],
];

$app = new \Slim\App($config);

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write('{"success": true, "message": "HbbTV Backend"}');

    return $response;
});

$app->group('/users/{id:[0-9]+}', function (App $app) {
    $app->map(['GET', 'DELETE', 'PATCH', 'PUT'], '', function ($request, $response, $args) {
        // Find, delete, patch or replace user identified by $args['id']
    })->setName('user');
    $app->get('/reset-password', function ($request, $response, $args) {
        // Route for /users/{id:[0-9]+}/reset-password
        // Reset the password for user identified by $args['id']
    })->setName('user-password-reset');
    $app->post('/ticket/new', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $ticket_data = [];
        $ticket_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $ticket_data['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);
    });
});


$app->run();
