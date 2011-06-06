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

class Visualize_ConfigController extends Visualize_AppController
{
   public $_moduleForms=array('Config');
   public $_components=array('Utility', 'Date');
   public $_moduleModels=array();

   
   /** index action*/
   function indexAction()
    {
    if(!$this->logged||!$this->userSession->Dao->getAdmin()==1)
      {
      throw new Zend_Exception("You should be an administrator");
      }
    
    $module = 'visualize';
    
    if(file_exists(BASE_PATH."/core/configs/api.local.ini"))
      {
      $applicationConfig = parse_ini_file(BASE_PATH."/core/configs/".$module.".local.ini", true);
      }
    else
      {
      $applicationConfig = parse_ini_file(BASE_PATH.'/modules/'.$module.'/configs/module.ini', true);
      }
    $configForm = $this->ModuleForm->Config->createConfigForm();
    
    $formArray = $this->getFormAsArray($configForm);    
    $formArray['useparaview']->setValue($applicationConfig['global']['useparaview']);
    
    $this->view->configForm = $formArray;
    
    if($this->_request->isPost())
      {
      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      $submitConfig = $this->_getParam('submitConfig');
      if(isset($submitConfig))
        {
        if(file_exists(BASE_PATH."/core/configs/".$module.".local.ini.old"))
          {
          unlink(BASE_PATH."/core/configs/".$module.".local.ini.old");
          }
        if(file_exists(BASE_PATH."/core/configs/".$module.".local.ini"))
          {
          rename(BASE_PATH."/core/configs/".$module.".local.ini",BASE_PATH."/core/configs/".$module.".local.ini.old");
          }
        $applicationConfig['global']['useparaview'] = $this->_getParam('useparaview');
        $this->Component->Utility->createInitFile(BASE_PATH."/core/configs/".$module.".local.ini", $applicationConfig);
        echo JsonComponent::encode(array(true, 'Changed saved'));
        }
      }
    } 
    
}//end class