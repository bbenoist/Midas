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

/** Index core controller for the landingpage module. */
class Landingpage_IndexCoreController extends Landingpage_AppController
{
    public $_moduleModels = array('Text');
    public $_components = array('Utility');

    /**
     * Index Action (first action when we access the application).
     */
    public function init()
    {
    }

    /** index action */
    public function indexAction()
    {
        $textDaos = $this->Landingpage_Text->getAll();
        if (isset($textDaos[0])) {
            $textDao = $textDaos[0];
            $this->view->landingText = UtilityComponent::markdown(htmlspecialchars($textDao->getText(), ENT_COMPAT, 'UTF-8'));
        } else {
            $this->callCoreAction();
        }
    }
}
