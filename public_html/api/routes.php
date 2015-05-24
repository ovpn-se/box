<?php

$app->post('/authenticate',    'API\User:Authenticate');
$app->get('/datacenter',       'API\OVPN:getBestDatacenter');
$app->get('/datacenter/:slug', 'API\OVPN:getBestServer');

$app->get('/traffic',          'API\Traffic:get');
$app->get('/traffic/history/:span', 'API\Traffic:getHistorical');
$app->get('/ip',               'API\Traffic:externalIP');

$app->post('/connect',         'API\OVPN:connect');
$app->get('/connected',        'API\OVPN:isConnected');

$app->post('/disconnect',      'API\OVPN:disconnect');

$app->get('/update', 'API\Update:get');
$app->post('/update', 'API\Update:execute');