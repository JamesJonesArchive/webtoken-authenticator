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

/**
 * Web Token Action
 *
 * @category usf-it
 * @package webtoken-authenticator
 * @author Eric Pierce <epierce@usf.edu>
 * @author James Jones <james@mail.usf.edu>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link https://github.com/USF-IT/webtoken-authenticator
 */
class WebTokenAction extends \USF\IdM\AuthTransfer\BasicAuthServiceAction {
    /**
     * Handles a Token Request
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function login(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        $username = self::getFirstElement($request->getHeader('AUTH_PRINCIPAL')); // Get the username 
        if($this->settings['webtoken']['enabled'] ?? TRUE) {
            try {
                $appId  = $this->getServiceParam($request,$args) ?? '';
            } catch (\Exception $ex) {
                $this->logger->error("Request for Web Token Login - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            try {
                $spaURL = $this->getRedirectParam($request,$args) ?? '';                
            } catch (\Exception $ex) {
                $this->logger->error("Request for Web Token Login - Invalid SPA URL: ${spaURL}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Redirect URL required." ],400);
            }
            // Lookup appId in ApplicationRegistry
            $application = $this->authService->findApplicationinRegistry($appId);
            // Return an error message if the appID is not found
            if (empty($application)) {
                $this->logger->error("Request for Web Token Login - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            // Return an error message if the spaURL is empty or not a valid URL
            if (! $this->authService->isValidUrl($spaURL)) {
                $this->logger->error("Request for Web Token Login - Invalid SPA URL: ${spaURL}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid SPA URL required." ],400);                
            }
            $this->logger->info("REDIRECT|${username}|Web Token login for application: ${appId}.  Redirecting to ${spaURL}|WEBTOKEN_LOGIN");
            // Redirect to the location of the Single-Page Application
            return $response->withRedirect($spaURL);
        } else {
            $this->logger->info("Request for Web Token Login - Disabled|WEBTOKEN_DISABLED");
            return $response->withRedirect('/disabled');                        
        }
    }
    /**
     * Handles a Token Request
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function request(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        $username = self::getFirstElement($request->getHeader('AUTH_PRINCIPAL')); // Get the username 
        if($this->settings['webtoken']['enabled'] ?? TRUE) {
            try {
                $appId  = $this->getServiceParam($request,$args) ?? '';
            } catch (\Exception $ex) {
                $this->logger->error("Request for Web Token Login - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            // Lookup appId in ApplicationRegistry
            $application = $this->authService->findApplicationinRegistry($appId);
            // Return an error message if the appID is not found
            if (empty($application)) {
                $this->logger->error("Request for Web Token Login - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            // Get additional info about the user that will be put into the token
            $extraAttributes = [];
            foreach ($application['attributes'] as $key) {
                $val = $request->getHeader('AUTH_ATTR_'.strtoupper($key));
                if(!empty($val)) {
                    $extraAttributes[$key] = $val;
                }
            }
            // Get a Signed Token for this user/application
            $token = $this->authService->getSignedToken($application,$username,$extraAttributes);
            
            $this->logger->debug("Token generated: ${token}");
            $this->logger->info("Web Token Created for application: ${appId}|WEBTOKEN_CREATED");
            
            // Return the Token
            $body = $response->getBody();
            $body->write($token);
            return $response->withBody($body)->withHeader('Content-type', 'application/json');
        } else {
            $this->logger->info("Request for Web Token Creation - Disabled|WEBTOKEN_DISABLED");
            return $response->withRedirect('/disabled');            
        }
    }
    /**
     * Validate a Token
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function validate(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        if($this->settings['webtoken']['enabled'] ?? TRUE) {
            try {
                $appId  = $this->getServiceParam($request,$args) ?? '';
            } catch (\Exception $ex) {
                $this->logger->error("Request for Web Token Validation - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            try {
                $token  = $this->getTokenParam($request,$args) ?? '';
            } catch (\Exception $ex) {
                $this->logger->error("Request for Web Token Validation - Token required.|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Token required." ],400);
            }
            // NOTICE - RoleCheck is unimplemented (for now)
            $roleCheck  = $this->getRoleParam($request,$args) ?? '';
            // Lookup appId in ApplicationRegistry
            $application = $this->authService->findApplicationinRegistry($appId);
            // Return an error message if the appID is not found
            if (empty($application)) {
                $this->logger->error("Request for Web Token Validation - Invalid Application ID: ${appId}|BAD_PARAMS"); 
                return $response->withJson(['result' => 'error', 'reason' => "Valid Application ID required." ],400);
            }
            try {
                $decoded = JWT::decode($token, $application['sharedSecret'], [ $this->settings['webtoken']['signingAlgorithm'] ]);                
                if($decoded->exp < \time() || $decoded->iat > \time()) {
                    throw \Exception;
                }
                $this->logger->info($decoded->sub."|Web Token Validated for application: ${appId}|WEBTOKEN_VALIDATE");
                return $response->withJson((array) $decoded);
            } catch (\Exception $ex) {
                $this->logger->error("UNAUTHENTICATED|Request for Web Token Validation - Invalid token: ${token}|WEBTOKEN_INVALID"); 
                return $response->withJson(['result' => 'error', 'reason' => "Invalid Token" ],401);
            }
        } else {
            $this->logger->info("Request for Web Token Validation - Disabled|WEBTOKEN_DISABLED");
            return $response->withRedirect('/disabled');
        }
    }
    /**
     * Handles authentication using WebTokens
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function dispatch(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
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
            'page_title' => 'WebToken | Disabled'  // This will used in the <title> element on the page
        ];

        return $this->view->render($response, 'disabled.html', $view_attr);
    }
    /**
     * Returns the first element of a passed array or empty string
     * 
     * @param type $arr
     * @return string
     */
    public static function getFirstElement($arr) {
        if(empty($arr)) {
            return '';
        } else {
            \reset($arr);
            return \current($arr);
        }
    }
    /**
     * Retrieves the redirect url either from the arguments or request body
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $args
     * @return string
     * @throws \Exception
     */
    private function getRedirectParam(\Psr\Http\Message\ServerRequestInterface $request, array $args) {
        if (isset($args['redirectURL'])){
            return $args['redirectURL'];
        } else {
            $parsedBody = $request->getParsedBody();
            if(isset($parsedBody['redirectURL'])) {
                return $parsedBody['redirectURL'];
            } else {
                throw new \Exception;
            }
        }
    }
    /**
     * Retrieves the service either from the arguments or request body
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $args
     * @return string
     * @throws \Exception
     */
    private function getServiceParam(\Psr\Http\Message\ServerRequestInterface $request, array $args) {
        if (isset($args['service'])){
            return $args['service'];
        } else {
            $parsedBody = $request->getParsedBody();
            if(isset($parsedBody['service'])) {
                return $parsedBody['service'];
            } else {
                throw new \Exception;
            }
        }
    }
    /**
     * Retrieves the token either from the arguments or request body
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $args
     * @return string
     * @throws \Exception
     */
    private function getTokenParam(\Psr\Http\Message\ServerRequestInterface $request, array $args) {
        if (isset($args['token'])){
            return $args['token'];
        } else {
            $parsedBody = $request->getParsedBody();
            if(isset($parsedBody['token'])) {
                return $parsedBody['token'];
            } else {
                throw new \Exception;
            }
        }
    }
    /**
     * Retrieves the role either from the arguments or request body or simply returns an empty string
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $args
     * @return string
     */
    private function getRoleParam(\Psr\Http\Message\ServerRequestInterface $request, array $args) {
        if (isset($args['role'])){
            return $args['role'];
        } else {
            $parsedBody = $request->getParsedBody();
            if(isset($parsedBody['role'])) {
                return $parsedBody['role'];
            } else {
                return '';
            }
        }
    }
}
