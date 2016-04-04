<?php

/**
 * Copyright 2015 University of South Florida
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

namespace USF\IdM\AuthTransfer\WebToken\Action;

class HomeActionTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setup()
    {

        $config = new \USF\IdM\UsfConfig(__DIR__ . '/config');

        $app = new \Slim\App(['settings' => $config->slimSettings]);
        // Set up dependencies
        include __DIR__ . '/../app/dependencies.php';
        // Register middleware
        include __DIR__ . '/../app/middleware.php';
        // Register routes
        include __DIR__ . '/../app/routes.php';
        $this->app = $app;
    }

    public function setRequest($method = 'GET', $uri = '/', $other = [])
    {
        // Prepare request and response objects
        $base = [
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => $method
        ];
        $env = \Slim\Http\Environment::mock(array_merge($base, $other));
        $uri = \Slim\Http\Uri::createFromEnvironment($env);
        $headers = \Slim\Http\Headers::createFromEnvironment($env);
        $cookies = (array)new \Slim\Collection();
        $serverParams = (array)new \Slim\Collection($env->all());
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        return new \Slim\Http\Request('GET', $uri, $headers, $cookies, $serverParams, $body);
    }

    public function testHomeAction()
    {
        $req = $this->setRequest('GET', '/');
        $res = new \Slim\Http\Response;

        // Invoke app
        $app = $this->app;
        $resOut = $app($req, $res);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $resOut);
        $this->assertContains('<title>WebToken | Main</title>', (string) $res->getBody());
    }

}
