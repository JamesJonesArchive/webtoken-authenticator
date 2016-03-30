<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace USF\IdM\AuthTransfer\AuthToken\Action;

/**
 * Description of CAS AuthTokenAction
 *
 * @author James Jones <james@mail.usf.edu>
 */
class AuthTokenAction extends \USF\IdM\AuthTransfer\BasicAuthServiceAction {
    /**
     * Handles authentication using CAS AuthTokens
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return type
     */
    public function dispatch(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args) {
        if($this->settings['casToken']['enabled'] ?? TRUE) {
            $username = $this->getNetid($request); // Get the username 
            $attributeMap = [
                "username" => $username,               
                "GivenName" => self::getFirstElement($this->getAttributeByName($request, 'GivenName')), //GivenName can be a list of names (first, middle), so just grab the first name
                "FamilyName" => self::getFirstElement($this->getAttributeByName($request, 'Surname')),
                "Email" => $this->getAttributeByName($request, 'mail'),
                "ProviderName" => "UsfNetid"
            ];
            
            // Default URL the user should be sent to after login - can be overridden by sending 'url' GET parameter
            $redirectURL = $args["url"] ?? $this->settings['casToken']['redirectURL'];
            
            $key = $this->settings['casToken']['key'];
            
            $tokenData = $this->authService->getToken($attributeMap,$key['value']);
                        
            // A128CBC-HS256
            $casAuthURL = $this->authService->getRedirectUrl([
                "json_data" => $tokenData['json'],
                "auth_token" => $tokenData['final'],
                "casURL" => $this->settings['casToken']['casURL'],
                "username" => $attributeMap['username'],
                "token_service" => $key['name'],
                "service" => $redirectURL
            ]);
            
            // The result was a URL for the CAS Token Auth Application
            $this->logger->info("REDIRECT|${username}|${$casAuthURL}");
            return $response->withRedirect($casAuthURL);            
        } else {
            $this->logger->error("Request for CAS AuthToken - Disabled|AUTHTOKEN_DISABLED"); 
            return $this->view->render($response, 'error.html', ['disabled' => TRUE, 'statusText' => "Requests for CAS AuthToken are disabled" ]);            
        }
    }
    public static function getFirstElement($arr) {
        if(empty($arr)) {
            return '';
        } else {
            \reset($arr);
            return \current($arr);
        }
    }
}
