<?php
use PHPUnit\Framework\TestCase;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

final class ControllerTest extends TestCase
{
    // This test is used to make sure PHPUnit is setup correct
    public function testAlwaysPasses()
    {
        $this->assertEquals(true,true);
    }

    // Test to make sure the internal PHP server is up and running
    public function testPhpWebserverActive()
    {
        $result = file_get_contents("http://localhost:9000");
        $this->assertNotFalse($result,"PHP Internal server does not appear to be started");
    }

    // Test to make sure Selenium is up and running
    // Webdriver basics at https://github.com/php-webdriver/php-webdriver/blob/master/example.php
    public function testPuPHPeteer()
    {
        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch();
        $page = $browser->newPage();
        $page->goto('http://localhost:9000');
        $title = strtolower($page->title());
        $browser->close();
        $passed = strpos($title,"updater") !== false;
        $this->assertTrue($passed,"Puppeteer failed.");
    }
}