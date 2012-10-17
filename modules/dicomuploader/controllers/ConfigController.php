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

class Dicomuploader_ConfigController extends Dicomuploader_AppController
{
   public $_moduleComponents = array('Uploader');
   public $_moduleForms=array('Config');
   public $_components=array('Utility', 'Date');

   /** index action*/
   function indexAction()
    {
    if(!$this->logged || !$this->userSession->Dao->getAdmin() == 1)
      {
      throw new Zend_Exception("You should be an administrator");
      }

    if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini"))
      {
      $applicationConfig = parse_ini_file(
        BASE_PATH."/core/configs/".$this->moduleName.".local.ini", true);
      }
    else
      {
      $applicationConfig = parse_ini_file(
        BASE_PATH.'/modules/'.$this->moduleName.'/configs/module.ini', true);
      }
    $configForm = $this->ModuleForm->Config->createConfigForm();

    $formArray = $this->getFormAsArray($configForm);
    $formArray['dcm2xml']->setValue($applicationConfig['global']['dcm2xml']);
    $formArray['storescp']->setValue($applicationConfig['global']['storescp']);
    $formArray['storescp_port']->setValue(
      $applicationConfig['global']['storescp_port']);
    $formArray['storescp_study_timeout']->setValue(
      $applicationConfig['global']['storescp_study_timeout']);
    if(!empty($applicationConfig['global']['receptiondir']))
      {
      $formArray['receptiondir']->setValue(
        $applicationConfig['global']['receptiondir']);
      }
    else
      {
      $default_dir = $this->ModuleComponent->Uploader->getDefaultReceptionDir();
      $formArray['receptiondir']->setValue($default_dir);
      }
    $formArray['pydas_dest_folder']->setValue(
      $applicationConfig['global']['pydas_dest_folder']);
    $this->view->configForm = $formArray;

    if($this->_request->isPost())
      {
      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      $submitConfig = $this->_getParam('submitConfig');
      if(isset($submitConfig))
        {
        if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old"))
          {
          unlink(BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old");
          }
        if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini"))
          {
          rename(BASE_PATH."/core/configs/".$this->moduleName.".local.ini",BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old");
          }
        $applicationConfig['global']['dcm2xml'] = $this->_getParam('dcm2xml');
        $applicationConfig['global']['storescp'] = $this->_getParam('storescp');
        $applicationConfig['global']['storescp_port'] =
          $this->_getParam('storescp_port');
        $applicationConfig['global']['storescp_study_timeout'] =
          $this->_getParam('storescp_study_timeout');
        $applicationConfig['global']['receptiondir'] =
          $this->_getParam('receptiondir');
        $applicationConfig['global']['pydas_dest_folder'] =
          $this->_getParam('pydas_dest_folder');
        $this->Component->Utility->createInitFile(BASE_PATH."/core/configs/".$this->moduleName.".local.ini", $applicationConfig);
        echo JsonComponent::encode(array(true, 'Changed saved'));
        }
      }
    }

}//end class
