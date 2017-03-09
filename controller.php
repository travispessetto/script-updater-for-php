<?php
require_once("configsingleton.php");
header("content-type: application/json");
$action = $_GET["action"];
$controller = new Controller();
call_user_func(array($controller,$action));

class Controller
{
   public function CheckFilesAreWritable()
   {
      $config = ConfigSingleton::Instance();
      $updateFolder = realPath($config->update_folder);
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $updateFiles = explode(PHP_EOL,file_get_contents($versionUrl));
      $writable = true;
      for($i = 1; $i < count($updateFiles); $i++)
      {
          $fileData = explode("    ",$updateFiles[$i]);
          if(count($fileData) == 2)
          {
              $pathInfo = pathinfo($updateFolder.'/'.$fileData[0]);
              if(file_exists($updateFolder.'/'.$fileData[0]) && !is_writable($updateFolder.'/'.$fileData[0]) )
              {
                  $writable = false;
                  // abort the rest of the files to prevent being overriden
                  break;
              }
              else if(file_exists($pathInfo['dirname']) && !is_writable($pathInfo['dirname']))
              {
                  $writable = false;
              }
              else
              {
                // Start breaking down the pathInfo
                $pathParts = explode('/',$pathInfo['dirname']);
                // Make OS agnostic
                if(count($pathParts) == 1)
                {
                  $pathParts = explode("\\",$pathInfo['dirname']);
                }
                foreach($pathParts as $key=>$pathPart)
                {
                    if($key == 0)
                    {
                      $pathToCheck = $pathPart;
                    }
                    else
                    {
                      $pathToCheck .= '/'.$pathPart;
                    }
                    if(file_exists($pathToCheck))
                    {
                      if(!is_writable($pathToCheck))
                      {
                        $writable = false;
                      }
                      else
                      {
                        // this is so we can undo a previous disallowed read...like so if a user as permission in the file but not its predescors
                        $writable = true;
                      }

                    }
                    // if done with foreach and we are not writable be done with it
                    if(!$writable)
                    {
                       break;
                    }

                    // If the file does not exist we will assume writability
                    // is based on the last existing file.
                }
              }
          }
      }
      echo json_encode(array("writable"=>$writable));
   }

   public function InstallFiles()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $updateFiles = explode(PHP_EOL,file_get_contents($versionUrl));
      $updateFolder = realpath($config->update_folder).'/';
      for($i = 1; $i < count($updateFiles); $i++)
      {
         $fileData = explode("    ",$updateFiles[$i]);
         if(count($fileData) == 2)
         {
           $content = file_get_contents($config->version_url."/".$fileData[1]);
           $pathInfo = pathinfo($updateFolder.$fileData[0]);
           if(!file_exists($pathInfo['dirname']))
           {
             // true = recursive
             mkdir($pathInfo['dirname'],0755,true);
           }
           file_put_contents($updateFolder.$fileData[0],$content);
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
      $currentVersionNumbers = explode(".",$currentVersion);
      $updateVersionNumbers = explode(".",$updateVersion);
      $current = true;
      foreach($updateVersionNumbers as $key=>$value)
      {
        // Assume if the update version major, minor, patch, .... is longer than
        // the current version then it is new
        if(!array_key_exists($key,$currentVersionNumbers))
        {
           $current = false;
        }
        else if($value > $currentVersionNumbers[$key])
        {
           $current = false;
        }
      }
        echo json_encode(array("current"=>$current,"current_version"=>$currentVersion,
        "update_version"=>$updateVersion));
   }


}
