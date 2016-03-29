<?php
/**
 *
 * Configure URL routes
 * see: http://www.slimframework.com/docs/objects/router.html
 *
 */

// Application Home Page
$app->get('/', 'AuthTransfer\AuthToken\Action\HomeAction:dispatch')
    ->setName('homepage');

// Display images from a webservice.  The album id piece of URL is optional.
//$app->get('/example[/{album_id}]', 'AuthTransfer\AuthToken\Action\ExampleAction:dispatch')
//    ->setName('displayPlaceholderImages');

// If you need to use POST or PUT you can config the route like this.
//$app->post('/example', 'AuthTransfer\AuthToken\Action\ExampleAction:runThisMethod')
//    ->setName('examplePOST');

// Gartner handling
$app->get('/authtoken', 'AuthTransfer\AuthToken\Action\AuthTokenAction:dispatch')
    ->setName('authtoken');