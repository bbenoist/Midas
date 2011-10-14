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

require_once BASE_PATH.'/library/KWUtils.php';


/**
 * KWUtils tests
 */


class KWUtilsTest extends ControllerTestCase
  {

  /**
   * helper function to return Midas configured temp directory
   * @return midas temp dir
   */
  protected function getTempDirectory()
    {
    include_once BASE_PATH.'/core/GlobalController.php';
    $controller = new MIDAS_GlobalController($this->request, $this->response);
    return $controller->getTempDirectory();
    }

  /** tests mkDir function */
  public function testMkDir()
    {
    $tmpDir = $this->getTempDirectory() . '/KWUtilsTest';
    $this->assertTrue(KWUtils::mkDir($tmpDir));
    // now clean up
    KWUtils::recursiveRemoveDirectory($tmpDir);
    }

  /** tests createSubDirectories function */
  public function testCreateSubDirectories()
    {
    // test creating directories, do this in the tmp dir
    //
    // create a nested set of directories
    $tmpDir = $this->getTempDirectory() . '/';
    $subDirs = array("KWUtilsTest", "1", "2", "3");
    $outDir = KWUtils::createSubDirectories($tmpDir, $subDirs);

    // now check that all the subdirs have been created

    // according to what we wanted
    $this->assertFileExists($tmpDir);
    // and what we got back
    $this->assertFileExists($outDir);

    $currDir = $tmpDir;
    foreach($subDirs as $subdir)
      {
      $currDir = $currDir . '/' . $subdir;
      $this->assertFileExists($currDir);
      $this->assertTrue(is_dir($currDir));
      }

    $topDir = $this->getTempDirectory() . '/KWUtilsTest';
    KWUtils::recursiveRemoveDirectory($topDir);
    }


  /** tests exec function */
  public function testExec()
    {
    // not sure how to test this exactly, for now create a tmp dir, check
    // the value of pwd in it

    // create a tmp dir for this test
    $execDir = $this->getTempDirectory() . '/KWUtilsTest';
    mkdir($execDir);
    $cmd = 'pwd';
    $chdir = $execDir;
    KWUtils::exec($cmd, $output, $chdir, $returnVal);
    // $output should have one value, the same as execDir

    // yuck, need to do a bit of munging to get around tests/.. in BASE_PATH
    $execDir = str_replace('tests/../', '', $execDir);
    // and now replace any // with /,
    // the // doesn't affect functionality on the
    // filesystem, but will cause string inequality
    $execDir = str_replace('//', '/', $execDir);

    $this->assertEquals($execDir, $output[0]);
    // returnVal should be 0
    $this->assertEquals($returnVal, 0);
    // now clean up the tmp dir
    rmdir($execDir);
    }

  /** tests appendStringIfNot function */
  public function testAppendStringIfNot()
    {
    // try one that doesn't have the suffix:
    $subject = 'blah';
    $ext = '.exe';
    $subject = KWUtils::appendStringIfNot($subject, $ext);
    $this->assertEquals($subject, 'blah.exe');
    // now try one that already has the suffix
    $subject = 'blah';
    $ext = '.exe';
    $subject = KWUtils::appendStringIfNot($subject, $ext);
    $this->assertEquals($subject, 'blah.exe');
    }

  /** tests findApp function */
  public function testFindApp()
    {
    // first try something that should be in the path, php, and check that it
    // is executable
    $pathToApp = KWUtils::findApp('php', true);
    // now try something that is unlikely to be in the path
    try
      {
      $pathToApp = KWUtils::findApp('php_exe_that_is_vanishingly_likley_to_be_in_the_path', true);
      $this->fail('Should have caught exception but did not, testFindApp');
      }
    catch(Zend_Exception $ze)
      {
      // if we end up here, that is the correct behavior
      $this->assertTrue(true);
      }
    }

  /** tests isExecutable function */
  public function testIsExecutable()
    {
    // this is tricky to test, as it is hard to make assumptions that hold
    // up across platforms
    //
    // for now assume that 'pwd' will not be found
    $this->assertFalse(KWUtils::isExecutable('pwd', false));
    // but 'pwd' will be found in the path
    $this->assertTrue(KWUtils::isExecutable('pwd', true));
    }

  /** tests prepareExecCommand function */
  public function testPrepareExecCommand()
    {
    $returnVal = KWUtils::prepareExecCommand('php', array('blah1', 'blah2', 'blah3'));
    $appPath = KWUtils::findApp('php', true);
    $this->assertEquals($returnVal, "'".$appPath."' 'blah1' 'blah2' 'blah3'");
    }

  /** tests recursiveRemoveDirectory function */
  public function testRecursiveRemoveDirectory()
    {
    // create a two-level directory
    $testParentDir = $this->getTempDirectory() . '/KWUtilsParentDir';
    mkdir($testParentDir);
    $testChildDir = $this->getTempDirectory() . '/KWUtilsParentDir/ChildDir';
    mkdir($testChildDir);
    copy(BASE_PATH.'/tests/testfiles/search.png', $testChildDir.'/testContent.png');
    $this->assertTrue(file_exists($testChildDir.'/testContent.png'));

    // recursively remove the directory
    KWUtils::recursiveRemoveDirectory($testParentDir);
    $this->assertFalse(file_exists($testParentDir));
    }

  }
