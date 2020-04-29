<?php

class Language
{
  protected function __construct()
  {
      $lang = "en";
      if(is_set($_SERVER) && array_key_exists("HTTP_ACCEPT_LANGUAGE",$_SERVER))
      {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
      }
      $langFile = dirname(__FILE__)."/php/$lang.php";
      if(file_exists($langFile))
      {
        require_once($langFile);
      }
      else
      {
        require_once(dirname(__FILE__)."/php/en.php");
      }
      $this->message = $message;
  }
  private function __close()
  {

  }
  private function __wakeup()
  {

  }

  public function __get($name)
  {
    if(array_key_exists($name,$this->message))
    {
      return $this->message[$name];
    }
    else
    {
      return null;
    }
  }

  public function LanguageCode()
  {
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    return $lang;
  }

  public static function Instance()
  {
    static $instance = null;
    if($instance === null)
    {
      $instance = new Language();
    }
    return $instance;
  }
}
