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

require_once BASE_PATH.'/core/controllers/components/UtilityComponent.php';
/** notification manager*/
class Notification extends MIDAS_Notification
  {
  public $_components = array('Utility');
  public $_models = array('User', 'Item');

  /** init notification process*/
  public function init()
    {
    $this->addCallBack('CALLBACK_CORE_GET_DASHBOARD', 'getDasboard');
    }//end init

  /** generate Dasboard information */
  public function getDasboard()
    {
    $return = array();
    $return['Config Folder Writable'] = array(is_writable(BASE_PATH.'/core/configs'));
    $return['Data Folder Writable'] = array(is_writable(BASE_PATH.'/data'));
    // pass in empty string since we want to check the overall root temp directory
    $return['Temporary Folder Writable'] = array(is_writable(UtilityComponent::getTempDirectory('')));

    return $return;
    }//end _getDasboard
  } //end class
?>
