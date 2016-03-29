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
            // Get the username
            $username = $this->getNetid($request);
            $firstname = self::getFirstElement($this->getAttributeByName($request, 'GivenName'));
            $lastname = self::getFirstElement($this->getAttributeByName($request, 'Surname'));
            
            
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
