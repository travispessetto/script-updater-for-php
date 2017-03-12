<?php
require_once("configsingleton.php");
require_once("./langs/language.php");
require_once("./lib/spyc/spyc.php");
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
      $sysc = Spyc::YAMLLoad($this->GetUpdateFile);
      $writable = $this->CheckUpdateFilesWritable($sysc);
      if($writable)
      {
        $writable = $this->CheckDeleteFilesWritable($sync);
      }
      echo json_encode(array("writable"=>$writable));
   }

   public function CheckUpdateFileExists()
   {
     $config = ConfigSingleton::Instance();
     $versionUrl = $config->version_url.'/'.$config->version_file;
     $file = file_get_contents($versionUrl);
     $exists = true;
     if($file === false)
     {
       $exists = false;
     }
     echo json_encode(array('exists'=>$exists,'url'=>$versionUrl));
   }

   public function InstallFiles()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $updateFiles = Spyc::YAMLLoad($versionUrl);
      $updateFolder = realpath($config->update_folder).'/';
      for($i = 0; $i < count($updateFiles['add']); $i++)
      {
           $content = file_get_contents($config->version_url."/".$file[$i]['remote']);
           $pathInfo = pathinfo($updateFolder.$file[$i]['local']);
           if(!file_exists($pathInfo['dirname']))
           {
             // true = recursive
             mkdir($pathInfo['dirname'],0755,true);
           }
           file_put_contents($updateFolder.$file[$i]['local'],$content);
      }
      for($i = 0; $i < count($updateFolder.$updateFiles['delete']); $i++)
      {
        if(is_dir($updateFolder.$updateFiles['delete'][$i]))
        {
          rmdir($updateFolder.$updateFiles['delete'][$i]);
        }
        else
        {
          unlink($updateFolder.$updateFiles['delete'][$i]);
        }
      }

      echo json_encode(array());
   }

   public function UpdateVersion()
   {
     $config = ConfigSingleton::Instance();
     $versionUrl = $config->version_url.'/'.$config->version_file;
     $updateVersion = explode(PHP_EOL,$this->GetUpdateFile())[0];
     file_put_contents("version.txt",$updateVersion);
     echo json_encode(array());
   }

   public function VersionIsCurrent()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $currentVersion = $spyc['version'];
      $updateVersion = explode(PHP_EOL,$this->GetUpdateFile())[0];
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

   private function CheckFileIsWritable($file)
   {
     $writable = true;
     $pathInfo = pathinfo($updateFolder.'/'.$file);
     if(file_exists($updateFolder.'/'.$file) && !is_writable($updateFolder.'/'.$file) )
     {
         $writable = false;
         // abort the rest of the files to prevent being overriden
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

           // If the file does not exist we will assume writability
           // is based on the last existing file.
       }
     }
     return $writable;
   }

   private function CheckDeleteFilesWritable($delete)
   {
     $writable = true;
     for($i = 0; $i < $update['delete']; $i++)
     {
       $file = $update['delete'][$i];
       $writable = $this->CheckFileIsWritable($file);
       if(!$writable)
       {
         break;
       }
     }
     return $writable;
   }

   private function CheckUpdateFilesWritable($update)
   {
     $writable = true;
     for($i = 0; $i < $update['add']; $i++)
     {
         $file = $update['add'][$i]['local'];
         $writable = $this->CheckFileIsWritable($file);
         if(!$writable)
         {
           return $writable;
         }
     }
    return $writable;
   }

   private function GetUpdateFile()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $contents = file_get_contents($versionUrl);
      if($contents === false)
      {
        header("HTTP/1.0 404 ".Language::Instance()->file_not_found);
        echo "File not found";
        exit();
      }
      else
      {
        return $contents;
      }
   }


}
