<?php
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

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
    public function testSeleniumSetup()
    {
        $host = 'http://localhost:4444';
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities);
        $driver->get("http://localhost:9000");
        $title = strtolower($driver->getTitle());
        $gotTitle = strpos($title,"updater") !== false;
        $this->assertTrue($gotTitle,"Selenium driver does not appear to be started");
    }
}