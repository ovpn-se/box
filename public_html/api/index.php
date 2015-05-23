<?php
require_once('../../essentials.php');


$app = new \Slim\Slim();
$app->config(array(
    'debug' => true,
    'log.level' => \Slim\Log::DEBUG,
    'log.enabled' => true,
));
$app->response->header('Content-Type', 'Content-Type: application/json');
$app->notFound(function() use ($app) {
    $app->halt(404, json_encode(array('error' => 'API method unknown')));

});

// Include all routes
include DOCUMENT_ROOT . '/public_html/api/routes.php';

$app->run();