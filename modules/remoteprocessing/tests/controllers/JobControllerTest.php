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

/** JobControllerTest */
class Remoteprocessing_JobControllerTest extends ControllerTestCase
{
    /** set up tests */
    public function setUp()
    {
        $this->setupDatabase(array('default', 'adminUser'));
        $this->_models = array('User', 'Item');
        $this->enabledModules = array('scheduler', 'remoteprocessing', 'api');
        parent::setUp();
    }

    /** test manage */
    public function testManage()
    {
        $usersFile = $this->loadData('User', 'adminUser');
        $userDao = $this->User->load($usersFile[0]->getKey());
        $itemFile = $this->loadData('Item', 'default');

        $this->dispatchUrl('/remoteprocessing/job/manage?itemId='.$itemFile[0]->getKey(), null, true);
        $this->dispatchUrl('/remoteprocessing/job/manage?itemId='.$itemFile[0]->getKey(), $userDao, false);

        /** @var Remoteprocessing_JobModel $jobModel */
        $jobModel = MidasLoader::loadModel('Job', 'remoteprocessing');
        $jobs = $jobModel->getRelatedJob($itemFile[0]);
        if (empty($jobs)) {
            $this->assertNotQuery('table#tableJobsList');
        } else {
            $this->assertQuery('table#tableJobsList');
        }
    }

    /** test init */
    public function testInit()
    {
        $usersFile = $this->loadData('User', 'adminUser');
        $userDao = $this->User->load($usersFile[0]->getKey());
        $itemFile = $this->loadData('Item', 'default');

        $this->resetAll();
        $this->dispatchUrl('/remoteprocessing/job/init?itemId='.$itemFile[0]->getKey(), $userDao, false);

        // create definition file
        $this->resetAll();
        $this->params = array();
        $this->params['results'][0] = 'foo;foo;foo;foo;foo;foo';
        $this->request->setMethod('POST');
        $this->dispatchUrl('/remoteprocessing/executable/define?itemId='.$itemFile[0]->getKey(), $userDao);

        $this->resetAll();
        $this->dispatchUrl('/remoteprocessing/job/init?itemId='.$itemFile[0]->getKey(), $userDao, false);

        //$this->assertQuery('#creatJobLink');
    }
}
