<?php
require_once dirname(__FILE__).'/../ControllerTestCase.php';
class FeedControllerTest extends ControllerTestCase
  {
  public function setUp()
    {
    $this->setupDatabase(array('default'));
    $this->_models=array('User', 'Feed');
    $this->_daos=array('User');
    parent::setUp();
    
    }

  /** test index*/
  public function testIndexAction()
    {
    $this->dispatchUrI("/feed");
    $this->assertController("feed");
    $this->assertAction("index");   
    
    // test if we have the feed public
    $this->assertQuery("div.feedElement[element='1']");
    $this->assertNotQuery("div.feedElement[element='3']");
    
    $this->resetAll();    
    // test when logged in
    $usersFile=$this->loadData('User','default');
    $userDao=$this->User->load($usersFile[0]->getKey());
    
    $this->dispatchUrI("/feed", $userDao);
    $this->assertController("feed");
    $this->assertAction("index");   
    
    $this->assertQuery("div.feedElement[element='1']");
    $this->assertQuery("div.feedElement[element='3']");
    }

  /** test delete feed */
  public function testDeleteajaxAction()
    { 
    // test if we get an error
    $this->dispatchUrI('/feed/deleteajax', null, true);
    
    $feedsFile=$this->loadData('Feed','default');
    $feedDao=$this->Feed->load($feedsFile[2]->getKey());
    $this->params['feed'] = $feedDao->getKey();  
    $this->dispatchUrI('/feed/deleteajax', null);
    
    $feedDao=$this->Feed->load($feedDao->getKey());
    if($feedDao == false)
      {
      $this->fail('Should not be able to delete feed '.$feedDao->getKey());
      }
      
    $this->params['feed'] = $feedDao->getKey();
    $usersFile=$this->loadData('User','default');
    $userDao=$this->User->load($usersFile[0]->getKey());
    $this->dispatchUrI('/feed/deleteajax', $userDao);
    
    $feedDao=$this->Feed->load($feedDao->getKey());
    if($feedDao != false)
      {
      $this->fail('Should be able to delete feed '.$feedDao->getKey());
      }
      
    $this->setupDatabase(array('default'));
    }
  }