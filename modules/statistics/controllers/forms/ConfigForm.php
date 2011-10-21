<?php
class Statistics_ConfigForm extends AppForm
{
 
  /** create  form */
  public function createConfigForm()
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/statistics/config/index')
          ->setMethod('post'); 
    
    $piwik = new Zend_Form_Element_Text('piwikurl');
    $piwikid = new Zend_Form_Element_Text('piwikid');
    $piwikapikey = new Zend_Form_Element_Text('piwikapikey');
    $report = new Zend_Form_Element_Checkbox('report');
    
    $submit = new  Zend_Form_Element_Submit('submitConfig');
    $submit ->setLabel('Save configuration');
     
    $form->addElements(array($report, $piwikapikey, $piwik, $piwikid, $submit));
    return $form;
    }
   
} // end class
?>