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

/** FolderpolicygroupModelBase */
abstract class FolderpolicygroupModelBase extends AppModel
{
  /** Constructor */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'folderpolicygroup';
    $this->_mainData = array(
          'folder_id' => array('type' => MIDAS_DATA),
          'group_id' => array('type' => MIDAS_DATA),
          'policy' => array('type' => MIDAS_DATA),
          'date' => array('type' => MIDAS_DATA),
          'folder' => array('type' => MIDAS_MANY_TO_ONE, 'model' => 'Folder', 'parent_column' => 'folder_id', 'child_column' => 'folder_id'),
          'group' => array('type' => MIDAS_MANY_TO_ONE, 'model' => 'Group', 'parent_column' => 'group_id', 'child_column' => 'group_id')
        );
    $this->initialize(); // required
    } // end __construct()

  /** Abstract functions */
  abstract function getPolicy($group, $folder);
  abstract function deleteGroupPolicies($group);
  abstract function computePolicyStatus($folder);

  /** delete */
  public function delete($dao)
    {
    $folder = $dao->getFolder();
    parent::delete($dao);
    if($dao->getGroupId() == MIDAS_GROUP_ANONYMOUS_KEY)
      {
      $this->computePolicyStatus($folder);
      }
    }//end delete

  /** create a policy
   * @return FolderpolicygroupDao*/
  public function createPolicy($group, $folder, $policy)
    {
    if(!$group instanceof GroupDao)
      {
      throw new Zend_Exception("Should be a group.");
      }
    if(!$folder instanceof FolderDao)
      {
      throw new Zend_Exception("Should be a folder.");
      }
    if(!is_numeric($policy))
      {
      throw new Zend_Exception("Should be a number.");
      }
    if(!$group->saved && !$folder->saved)
      {
      throw new Zend_Exception("Save the daos first.");
      }
    $policyGroupDao = $this->getPolicy($group, $folder);
    if($policyGroupDao !== false)
      {
      $this->delete($policyGroupDao);
      }
    $policyGroupDao = MidasLoader::newDao('FolderpolicygroupDao');
    $policyGroupDao->setGroupId($group->getGroupId());
    $policyGroupDao->setFolderId($folder->getFolderId());
    $policyGroupDao->setPolicy($policy);
    $this->save($policyGroupDao);

    if($policyGroupDao->getGroupId() == MIDAS_GROUP_ANONYMOUS_KEY)
      {
      $this->computePolicyStatus($folder);
      }
    return $policyGroupDao;
    }


} // end class FolderpolicygroupModelBase
