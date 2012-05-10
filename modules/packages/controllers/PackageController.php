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
/** package controller*/
class Packages_PackageController extends Packages_AppController
{
  public $_models = array('Item');
  public $_moduleModels = array('Application', 'Package', 'Project');

  /**
   * Render the page for editing package metadata on a specific package.
   * Write permission on the item is required.
   */
  public function manageAction()
    {
    $packageId = $this->_getParam('id');
    if(!isset($packageId))
      {
      throw new Zend_Exception('Must specify an id parameter');
      }
    $package = $this->Packages_Package->load($packageId);
    if(!$package)
      {
      throw new Zend_Exception('Invalid package id');
      }
    if(!$this->Item->policyCheck($package->getItem(), $this->userSession->Dao, MIDAS_POLICY_WRITE))
      {
      throw new Zend_Exception('Write permission required');
      }

    $this->view->package = $package;
    }

  /**
   * Called when the edit form is submitted
   */
  public function saveAction()
    {
    $packageId = $this->_getParam('packageId');
    if(!isset($packageId))
      {
      throw new Zend_Exception('Must set packageId parameter');
      }
    $package = $this->Packages_Package->load($packageId);
    if(!isset($package))
      {
      throw new Zend_Exception('Invalid packageId parameter');
      }
    if(!$this->Item->policyCheck($package->getItem(), $this->userSession->Dao, MIDAS_POLICY_WRITE))
      {
      throw new Zend_Exception('Write permission required');
      }
    $this->disableLayout();
    $this->disableView();

    $package->setOs($this->_getParam('os'));
    $package->setArch($this->_getParam('arch'));
    $package->setRevision($this->_getParam('revision'));
    $package->setSubmissiontype($this->_getParam('submissiontype'));
    $package->setPackagetype($this->_getParam('packagetype'));
    $package->setProductname($this->_getParam('productname'));
    $package->setCodebase($this->_getParam('codebase'));
    $package->setCheckoutdate($this->_getParam('checkoutdate'));
    $package->setRelease($this->_getParam('release'));
    $this->Packages_Package->save($package);

    echo JsonComponent::encode(array('message' => 'Changes saved', 'status' => 'ok'));
    }

  /**
   * Ajax action for getting the latest package of each package type for the given os and arch
   * @param os The os to match on
   * @param arch The arch to match on
   * @param applicationId The application id
   * @return (json) - The latest uploaded package of each installer type for the given os, arch, and application
   */
  public function latestAction()
    {
    $this->disableLayout();
    $this->disableView();
    echo JsonComponent::encode(array());
    }
}//end class
