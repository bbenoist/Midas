<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/

/** Tests the functionality of the user's API keys using the controllers */
class ApiKeyControllerTest extends ControllerTestCase
  {
  /** set up tests */
  public function setUp()
    {
    $this->setupDatabase(array('default')); //core dataset
    $this->setupDatabase(array('default'), 'api'); // module dataset
    $this->enabledModules = array('api');
    $this->_models = array('User');
    $this->_daos = array('User');
    parent::setUp();
    }

  /** Make sure changing a password changes the default api key */
  public function testChangePasswordChangesDefaultApiKey()
    {
    $usersFile = $this->loadData('User', 'default');
    $userDao = $this->User->load($usersFile[0]->getKey());
    
    $modelLoad = new MIDAS_ModelLoader();
    $userApiModel = $modelLoad->loadModel('Userapi', 'api');
    $userApiModel->createDefaultApiKey($userDao);
    $preKey = $userApiModel->getByAppAndUser('Default', $userDao)->getApikey();
    $this->assertEquals(strlen($preKey), 32);

    $this->params['oldPassword'] = 'test';
    $this->params['newPassword'] = 'test1';
    $this->params['newPasswordConfirmation'] = 'test1';
    $this->params['modifyPassword'] = 'modifyPassword';
    $this->request->setMethod('POST');

    $page = $this->webroot.'user/settings';
    $this->dispatchUrI($page, $userDao);
    
    $postKey = $userApiModel->getByAppAndUser('Default', $userDao)->getApikey();
    $this->assertNotEquals($preKey, $postKey);
    $passwordPrefix = Zend_Registry::get('configGlobal')->password->prefix;
    $this->assertEquals($postKey, md5($userDao->getEmail().md5($passwordPrefix.'test1').'Default'));
    }

  /** Make sure adding a new user adds a default api key */
  public function testNewUserGetsDefaultApiKey()
    {
    }
  }
