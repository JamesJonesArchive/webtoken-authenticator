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

use Slim\Views\Twig;
use Slim\Collection;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Main page
 *
 * @category usf-it
 * @package webtoken-authenticator
 * @author Eric Pierce <epierce@usf.edu>
 * @author James Jones <james@mail.usf.edu>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link https://github.com/USF-IT/webtoken-authenticator
 */
final class HomeAction
{
    private $view;
    private $logger;
    private $settings;

    /**
     * Class constructor
     *
     * @param Twig            $view      View object
     * @param LoggerInterface $logger    Log object
     * @param Collection      $settings  Slim Settings
     */
    public function __construct(Twig $view, LoggerInterface $logger, Collection $settings)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->settings = $settings;
    }

    /**
     * Run Controller
     *
     * @param ServerRequestInterface $request  PSR7 Request object
     * @param ResponseInterface      $response PSR7 Response object
     * @param array                  $args     Request arguments
     * @return ResponseInterface
     */
    public function dispatch(Request $request, Response $response, $args)
    {
        /**
         * If you need CAS authentication, make sure to update the `interceptUrlMap`
         * map in the config file.  The username will be available like this:
         *
         * $netid = $request->getHeaderLine('AUTH_PRINCIPAL');
         *
         * and the attributes:
         *
         * $usfid = $request->getHeaderLine('AUTH_ATTR_USFEDUUNUMBER');
         */

        $view_attr = [
            'page_title' => 'WebToken | Main'  // This will used in the <title> element on the page
        ];

        return $this->view->render($response, 'home.html', $view_attr);
    }
}
