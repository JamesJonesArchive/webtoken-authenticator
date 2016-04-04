<?php
/**
 *
 * Configure URL routes
 * see: http://www.slimframework.com/docs/objects/router.html
 *
 */

// Application Home Page
$app->get('/', 'AuthTransfer\WebToken\Action\HomeAction:dispatch')
    ->setName('homepage');

// Webtoken handling
$app->get('/disabled', 'AuthTransfer\WebToken\Action\WebTokenAction:dispatch')
    ->setName('disabled');

$app->get('/request','AuthTransfer\WebToken\Action\WebTokenAction:request')
    ->setName('request');

$app->post('/request','AuthTransfer\WebToken\Action\WebTokenAction:request')
    ->setName('request');

$app->get('/validate','AuthTransfer\WebToken\Action\WebTokenAction:validate')
    ->setName('validate');

$app->post('/validate','AuthTransfer\WebToken\Action\WebTokenAction:validate')
    ->setName('validate');

$app->get('/login','AuthTransfer\WebToken\Action\WebTokenAction:login')
    ->setName('validate');

$app->post('/login','AuthTransfer\WebToken\Action\WebTokenAction:login')
    ->setName('validate');

