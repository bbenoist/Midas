<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/

/** Handles grant authorization requests from the user agent */
class Oauth_AuthorizeController extends Oauth_AppController
{
  public $_models = array('User');
  public $_moduleModels = array('Client', 'Code');

  /**
   * Renders the oauth login screen. See http://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-4.1.1
   * @param response_type Should be set to "code".
   * @param client_id The identifier of the client
   * @param redirect_uri The redirect URI to redirect the end user to upon successful login
   * @param [scope] JSON-encoded array of scope constants (see api module constants). Defaults to ALL if not set
   * @param [state] Opaque value that will be passed back when redirecting user on success
   */
  function indexAction()
    {
    $this->disableLayout();

    $responseType = $this->_getParam('response_type');
    $redirectUri = $this->_getParam('redirect_uri');
    $scope = $this->_getParam('scope');
    $clientId = $this->_getParam('client_id');

    if(!isset($clientId))
      {
      throw new Zend_Exception('Must pass a client_id parameter', 400);
      }
    if(!isset($redirectUri))
      {
      throw new Zend_Exception('Must pass a redirect_uri parameter', 400);
      }
    if(!isset($scope))
      {
      $scope = JsonComponent::encode(array(MIDAS_API_PERMISSION_SCOPE_ALL));
      }
    if(!isset($responseType) || $responseType !== 'code')
      {
      throw new Zend_Exception('Only the "code" response type is supported currently', 400);
      }

    $client = $this->Oauth_Client->load($clientId);
    if(!$client)
      {
      throw new Zend_Exception('Invalid clientId', 400);
      }

    $scopeRegistry = Zend_Registry::get('permissionScopeMap');
    $scopeArray = JsonComponent::decode($scope);
    $scopeStrings = array();
    foreach($scopeArray as $scopeEntry)
      {
      $scopeStrings[] = $scopeRegistry[$scopeEntry];
      }
    $this->view->scopeStrings = $scopeStrings;
    $this->view->state = $this->_getParam('state');
    $this->view->scope = $scope;
    $this->view->redirectUri = $redirectUri;
    $this->view->client = $client;
    }

  /**
   * Submit login form.  Will redirect the user to the redirect_uri on success
   * @param redirect_uri
   * @param [state]
   */
  function submitAction()
    {
    $this->disableLayout();
    $this->disableView();

    $redirectUri = $this->_getParam('redirect_uri');
    $scope = $this->_getParam('scope');
    $clientId = $this->_getParam('client_id');
    $state = $this->_getParam('state');
    $login = $this->_getParam('login');
    $password = $this->_getParam('password');

    if(!isset($clientId))
      {
      throw new Zend_Exception('Must pass a client_id parameter', 400);
      }
    if(!isset($login))
      {
      throw new Zend_Exception('Must pass a login parameter', 400);
      }
    if(!isset($password))
      {
      throw new Zend_Exception('Must pass a password parameter', 400);
      }
    if(!isset($redirectUri))
      {
      throw new Zend_Exception('Must pass a redirect_uri parameter', 400);
      }
    if(!isset($scope))
      {
      $scope = JsonComponent::encode(array(MIDAS_API_PERMISSION_SCOPE_ALL));
      }

    $client = $this->Oauth_Client->load($clientId);
    if(!$client)
      {
      throw new Zend_Exception('Invalid clientId', 400);
      }

    $userDao = $this->User->getByEmail($login);
    if($userDao === false)
      {
      echo JsonComponent::encode(array('status' => 'error', 'message' => 'Invalid username or password'));
      return;
      }
    $instanceSalt = Zend_Registry::get('configGlobal')->password->prefix;
    $passwordHash = hash($userDao->getHashAlg(), $instanceSalt.$userDao->getSalt().$password);

    if($this->User->hashExists($passwordHash))
      {
      $codeDao = $this->Oauth_Code->create($userDao, $client, JsonComponent::decode($scope)); 

      $url = $redirectUri;
      $url .= strpos($redirectUri, '?') === false ? '?' : '&';
      $url .= 'code='.$codeDao->getCode();
      if($state)
        {
        $url .= '&state='.$state;
        }
      echo JsonComponent::encode(array('status' => 'ok', 'redirect' => $url));
      return;
      }
    else
      {
      echo JsonComponent::encode(array('status' => 'error', 'message' => 'Invalid username or password'));
      return;
      }
    }
  } // end class
?>
