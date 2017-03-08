<?php
require_once("configsingleton.php");
header("content-type: application/json");
$action = $_GET["action"];
$controller = new Controller();
call_user_func(array($controller,$action));

class Controller
{
   public function InstallFiles()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $updateFiles = explode(PHP_EOL,file_get_contents($versionUrl));
      for($i = 1; $i < count($updateFiles); $i++)
      {
         $fileData = explode("    ",$updateFiles[$i]);
         if(count($fileData) == 2)
         {
           $content = file_get_contents($config->version_url."/".$fileData[1]);
           file_put_contents($config->update_folder.$fileData[0],$content);
         }
      }
      echo json_encode(array());
   }

   public function UpdateVersion()
   {
     $config = ConfigSingleton::Instance();
     $versionUrl = $config->version_url.'/'.$config->version_file;
     $updateVersion = explode(PHP_EOL,file_get_contents($versionUrl))[0];
     file_put_contents("version.txt",$updateVersion);
     echo json_encode(array());
   }

   public function VersionIsCurrent()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $currentVersion = explode(PHP_EOL,file_get_contents('version.txt'))[0];
      $updateVersion = explode(PHP_EOL,file_get_contents($versionUrl))[0];
      if($currentVersion == $updateVersion)
      {
        echo json_encode(array("current"=>true,"version"=>$currentVersion));
      }
      else
      {
        echo json_encode(array("current"=>false,"version"=>$updateVersion));
      }
   }

}
