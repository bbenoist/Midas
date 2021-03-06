<?php
/*=========================================================================
 Midas Server
 Copyright Kitware SAS, 26 rue Louis Guérin, 69100 Villeurbanne, France.
 All rights reserved.
 For more information visit http://www.kitware.com/.

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

/** Tests the functionality of the user's API keys using the controllers */
class Api_KeyControllerTest extends ControllerTestCase
{
    /** set up tests */
    public function setUp()
    {
        $this->setupDatabase(array('default', 'userapi')); // core dataset
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

        $this->User->changePassword($userDao, 'test');
        $this->User->save($userDao);

        /** @var UserapiModel $userApiModel */
        $userApiModel = MidasLoader::loadModel('Userapi');
        $userApiModel->createDefaultApiKey($userDao);
        $preKey = $userApiModel->getByAppAndUser('Default', $userDao)->getApikey();
        $this->assertEquals(strlen($preKey), 32);

        $this->params['oldPassword'] = 'test';
        $this->params['newPassword'] = 'test1';
        $this->params['newPasswordConfirmation'] = 'test1';
        $this->params['modifyPassword'] = 'modifyPassword';
        $this->request->setMethod('POST');

        $page = $this->webroot.'user/settings';
        $this->dispatchUrl($page, $userDao);

        $postKey = $userApiModel->getByAppAndUser('Default', $userDao)->getApikey();
        $this->assertNotEquals($preKey, $postKey);
    }

    /** Make sure adding a new user adds a default api key */
    public function testNewUserGetsDefaultApiKey()
    {
        /** @var SettingModel $settingModel */
        $settingModel = MidasLoader::loadModel('Setting');
        $settingModel->setConfig('close_registration', 0);

        // Register a new user
        $this->params['email'] = 'some.user@server.com';
        $this->params['password1'] = 'midas';
        $this->params['password2'] = 'midas';
        $this->params['firstname'] = 'some';
        $this->params['lastname'] = 'user';
        $this->request->setMethod('POST');

        $page = $this->webroot.'user/register';
        $this->dispatchUrl($page);

        // Check that their default api key was created
        /** @var UserapiModel $userApiModel */
        $userApiModel = MidasLoader::loadModel('Userapi');
        $key = $userApiModel->getByAppAndEmail('Default', 'some.user@server.com')->getApikey();
        $this->assertNotEmpty($key);
    }
}
