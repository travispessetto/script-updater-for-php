<?php
use PHPUnit\Framework\TestCase;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

final class ControllerTest extends TestCase
{

    public function setUp() : void 
    {
      //$this->rrmdir("./scenarios");
    }

    public function tearDown() : void
    {
        //$this->rrmdir("./scenarios");
    }

    // This test is used to make sure PHPUnit is setup correct
    public function testAlwaysPasses()
    {
        $this->assertEquals(true,true);
    }

    // Test to make sure the internal PHP server is up and running
    public function testPhpWebserverActive()
    {
        $result = file_get_contents("http://localhost");
        $this->assertNotFalse($result,"PHP Internal server does not appear to be started");
    }

    public function testUpdateAvalibleLocalDirNoExistScenario()
    {
      $this->prepare_scenario("UpdateAvalibleLocalDirNoExist");
      $puppeteer = new Puppeteer(['read_timeout' => 300]); // seconds used here
      $browser = $puppeteer->launch();
      $page = $browser->newPage();
      $page->goto("http://localhost/scenarios/UpdateAvalibleLocalDirNoExist/target/",["timeout"=>300000]); //milliseconds used here
      $selector = $page->querySelectorAll(".waiting");
      $this->assertNotNull($selector,"Content:".PHP_EOL.$page->content());
      try
      {
        $selector = $page->tryCatch->waitForSelector("#updateVersion");
        $this->assertNotNull($selector,"Content:".PHP_EOL.$page->content());
      }
      catch(Nesk\Rialto\Exceptions\Node\Exception $ex)
      {
        $this->assertTrue(false,"Timeout occured, contents of page were:".PHP_EOL.$page->content());
      }

    }

    public function testRestorePrevVersion()
    {
      $this->prepare_scenario("RestorePrevVersion");
      $puppeteer = new Puppeteer(["read_timeout" => 300]);
      $browser = $puppeteer->launch();
      $page = $browser->newPage();
      $page->goto("http://localhost/scenarios/RestorePrevVersion/target/",["timeout"=>300000]);
      $selector = $page->querySelectorAll(".waiting");
      $this->assertNotNull($selector,"Content:".PHP_EOL.$page->content());
      try
      {
        $javascriptSelector = "#updateVersion";
        $selector = $page->tryCatch->waitForSelector($javascriptSelector);
        $this->assertNotNull($selector,"Page contents are:".PHP_EOL.$page->content());
        $page->click($javascriptSelector);
        $javascriptSelector = "#updateFinished";
        $selector = $page->tryCatch->waitForSelector($javascriptSelector);
        $this->assertNotNull($selector,"Page contents are:".PHP_EOL.$page->content());
        $page->reload();
        $selector = $page->querySelectorAll(".waiting");
        $this->assertNotNull($selector,"Content:".PHP_EOL.$page->content());
        $javascriptSelector = "#restoreVersion";
        $selector = $page->tryCatch->waitForSelector($javascriptSelector);
        $this->assertNotNull($selector,"Page contents are:".PHP_EOL.$page->content());
        $page->click($javascriptSelector);
        $javascriptSelector = "#restoreBackup-0.0.0";
        $selector = $page->tryCatch->waitForSelector($javascriptSelector);
        $this->assertNotNull($selector,"Page contents are:".PHP_EOL.$page->content());
        $page->click($javascriptSelector);
        $javascriptSelector = "#restorationFinished";
        $selector = $page->tryCatch->waitForSelector($javascriptSelector);
        $this->assertNotNull($selector,"Page contents are:".PHP_EOL.$page->content());
      }
      catch(Nesk\Rialto\Exceptions\Node\Exception $ex)
      {
        $this->assertTrue(false,"Timeout occured, contents of page were:".PHP_EOL.$page->content());
      }
    }

    private function prepare_scenario($scenario)
    {
      $workingDir = getcwd();
      $directory = "$workingDir/scenarios/$scenario/target";
      
      try
      {
        if(file_exists($directory))
        {
          $this->rrmdir($directory);
        }
        $this->assertTrue(mkdir($directory,0755,true),"Could not create folder $directory");
        $this->recurse_copy(realpath("$workingDir/src/"),"$workingDir/scenarios/$scenario/target");
        $this->assertTrue(unlink(realpath("$workingDir/scenarios/$scenario/target/config.php")),"Could not delete configuration file");
        $sourceConfig = realpath("$workingDir/tests/scenarios/$scenario/target/config.php");
        $targetConfig = "$workingDir/scenarios/$scenario/target/config.php";
        $this->assertTrue(copy($sourceConfig,$targetConfig),"Failed to copy $sourceConfig to $targetConfig");
        $this->assertTrue(file_exists("$workingDir/scenarios/$scenario/target/config.php"), "Configuration file does not exist");
        $this->recurse_copy(realpath("$workingDir/tests/scenarios/$scenario/source"),"$workingDir/scenarios/$scenario/source");
        $this->recurse_copy(realpath("$workingDir/tests/scenarios/$scenario/target"),$directory);
        sleep(20);
      }
      catch(Exception $ex)
      {
        $this->assertTrue(false,"Could not create folder $directory or file $targetConfig from $sourceConfig as ".exec('whoami')." because ".$ex->getMessage()." on line: ".$ex->getLine());
      }
    }

    private function recurse_copy($src,$dst) {
      $dir = opendir($src);
      @mkdir($dst);
      while(false !== ( $file = readdir($dir)) ) {
          if (( $file != '.' ) && ( $file != '..' )) {
              if ( is_dir($src . '/' . $file) ) {
                  $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
              }
              else {
                if(file_exists("$dst/$file"))
                {
                  unlink("$dst/$file");
                }
                  if(!empty(trim($file)))
                  {
                   copy($src . '/' . $file,$dst . '/' . $file);
                  }
              }
          }
      }
      closedir($dir);
  }


    private function rrmdir($dir) { 
        if (is_dir($dir)) { 
          $objects = scandir($dir);
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
              else
                unlink($dir. DIRECTORY_SEPARATOR .$object); 
            } 
          }
          rmdir($dir); 
        } 
    }

}