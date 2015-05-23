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



/*
$app->group('/api/transaction', function () use ($app) {

    // Skapa Billogram
    $app->post('/create/billogram', 'Controller\Transaction:processInvoice');

    // Webhook
    $app->post('/hook/billogram', 'Controller\Transaction:webhookBillogram');
    $app->post('/hook/bitpay',    'Controller\Transaction:webhookBitpay');

    $app->get('/hook/braintree/successful',   'Controller\Transaction:webhookBraintreeSuccessful');
    $app->get('/hook/braintree/unsuccessful', 'Controller\Transaction:webhookBraintreeUnsuccessful');

    $app->post('/hook/braintree/successful',   'Controller\Transaction:webhookBraintreeSuccessful');
    $app->post('/hook/braintree/unsuccessful', 'Controller\Transaction:webhookBraintreeUnsuccessful');

    $app->post('/',             'Controller\Transaction:processPayment');
    $app->post('/addons',       'Controller\Transaction:processAddonsPayment');
    $app->post('/identifier',   'Controller\Transaction:saveTemporaryOrder');
    $app->post('/swish',        'Controller\Transaction:processSwishPayment');
    $app->post('/swish/addons', 'Controller\Transaction:processSwishAddonsPayment');


    $app->post('/expired', 'Controller\Transaction:expired');
    $app->post('/failed',  'Controller\Transaction:failed');
    $app->post('/success', 'Controller\Transaction:success');

});*/