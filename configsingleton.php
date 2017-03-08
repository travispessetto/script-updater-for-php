<?php

class ConfigSingleton
{
      protected function __construct()
      {
        require_once("config.php");
        $this->config = $config;
      }

      private function __close()
      {

      }
      private function __wakeup()
      {

      }

      public function __get($name)
      {
        if(array_key_exists($name,$this->config))
        {
          return $this->config[$name];
        }
        else
        {
          return null;
        }
      }

      public static function Instance()
      {
        static $instance = null;
        if($instance === null)
        {
          $instance = new ConfigSingleton();
        }
        return $instance;
      }
}
