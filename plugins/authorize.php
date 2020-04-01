<?php

class Authorize
{
    public function ConstructorHook()
    {
        // authorization logic here...call exit if not
        // authorized.
        header('HTTP/1.0 403 Forbidden');
        exit();
    }
}