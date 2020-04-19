<?php
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    // This test is used to make sure PHPUnit is setup correct
    public function testAlwaysPasses()
    {
        $this->assertEquals(true,true);
    }

    public function testPhpWebserverActive()
    {
        $result = file_get_contents("http://localhost:9000");
        $this->assertNotFalse($result);
    }
}