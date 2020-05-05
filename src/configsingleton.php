<?php

class ConfigSingleton
{
      protected function __construct()
      {
        $config[] = array();
        require_once(__DIR__."/config.php");
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
          throw new new Exception("$name is not in the configuration array");
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
