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

    // Test to make sure Selenium is up and running
    // Webdriver basics at https://github.com/php-webdriver/php-webdriver/blob/master/example.php
    public function testPuPHPeteer()
    {
        $puppeteer = new Puppeteer(['read_timeout' => 300]); // seconds used here
        $browser = $puppeteer->launch();
        $page = $browser->newPage();
        $page->goto('http://localhost');
        $title = strtolower($page->title());
        $browser->close();
        $passed = strpos($title,"updater") !== false;
        $this->assertTrue($passed,"Puppeteer failed.");
    }

    public function testUpdateAvalibleDirNoExistScenario()
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

    private function prepare_scenario($scenario)
    {
      $directory = "./scenarios/$scenario/target";
      $workingDir = getcwd();
      try
      {
        $this->assertTrue(mkdir($directory,0755,true),"Could not create folder $directory");
        $this->recurse_copy(realpath("./src/"),"./scenarios/$scenario/target");
        unlink(realpath("./scenarios/$scenario/target/config.php"));
        copy(realpath("./tests/scenarios/$scenario/target/config.php"),"./scenarios/$scenario/target/config.php");
        $this->recurse_copy(realpath("./tests/scenarios/$scenario/source"),"./scenarios/$scenario/source");
      }
      catch(Exception $ex)
      {
        $this->assertTrue(false,"Could not create folder $directory in $cwd.");
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
                  copy($src . '/' . $file,$dst . '/' . $file);
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