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

  public function BackupFiles()
  {
    $zip = new ZipArchive();
    $currentVersion = explode(PHP_EOL,file_get_contents("version.txt"))[0];
    $filename = __DIR__."/backups/backup-$currentVersion.zip";
    if(!file_exists(__DIR__."/backups"))
    {
      mkdir(__DIR__."/backups");
    }
    if($zip->open($filename,ZipArchive::CREATE) !== TRUE)
    {
      echo json_encode(array('success'=>false));
    }
    else
    {
      $config = ConfigSingleton::Instance();
      $updateFiles = Spyc::YAMLLoad($this->GetUpdateFile());
      $updateFiles = $updateFiles['files'];
      $updateFolder = realpath($config->update_folder).'/';
      if(array_key_exists('add',$updateFiles))
      {
          $addFiles = $updateFiles['add'];
          for($i = 0; $i < count($addFiles); $i++)
          {
            if(file_exists($updateFolder.$addFiles[$i]['local']))
            {
              if($zip->addFile($updateFolder.$addFiles[$i]['local'],$addFiles[$i]['local']) === false)
              {
                $zip->close();
                unlink($filename);
                echo json_encode(array('success'=>false));
                exit();
              }
            }
          }
      }
      if(array_key_exists('delete',$updateFiles))
      {
        error_log("Delete files");
        $deleteFiles = $updateFiles['delete'];
        for($i = 0; $i < count($deleteFiles); $i++)
        {
          if(file_exists($updateFolder.$deleteFiles[$i]))
          {
            error_log("Add deleted file $deleteFiles[$i]");
            if($zip->addFile($updateFolder.$deleteFiles[$i],$deleteFiles[$i]) === false)
            {
              $zip->close();
              unlink($filename);
              echo json_encode(array('success'=>false));
              exit();
            }
          }
        }
      }
      else
      {
        error_log("No delete file");
      }
    }
    echo json_encode(array('success'=>true));

  }
   public function CheckFilesAreWritable()
   {
      $config = ConfigSingleton::Instance();
      $updateFolder = realPath($config->update_folder);
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $writable = $this->CheckUpdateFilesWritable($spyc);
      if($writable)
      {
        $writable = $this->CheckDeleteFilesWritable($spyc);
      }
      echo json_encode(array("writable"=>$writable));
   }

   public function CheckForBackups()
   {
     $config = ConfigSingleton::Instance();
     $updateFolder = realPath($config->update_folder);
     $exists = false;
     $files = glob("$updateFolder/backups/backup-*\.zip");
     if(count($files) > 0)
     {
        $exists = true;
     }
     echo json_encode(array('exists'=>$exists));
   }

   public function CheckForScripts()
   {
     $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
     $config = ConfigSingleton::Instance();
     $updateFolder = realPath($config->update_folder);
     $exists = true;
     if(!array_key_exists('scripts',$spyc))
     {
       $exists = false;
     }
     else
     {
       foreach($spyc['scripts'] as $script)
       {
         if(!file_exists($updateFolder.'/'.$script))
         {
           $exists = false;
           break;
         }
       }
     }
     echo json_encode(array('exists'=>$exists));
   }

   public function CheckRemoteFilesExist()
   {
    $config = ConfigSingleton::Instance();
    $updateFiles = Spyc::YAMLLoad($this->GetUpdateFile());
    $updateFiles = $updateFiles['files'];
     if(array_key_exists('add',$updateFiles))
      {
          $addFiles = $updateFiles['add'];
          for($i = 0; $i < count($addFiles); $i++)
          {
            $fileHeaders = @get_headers($config->version_url."/".$addFiles[$i]['remote']);
            if(!$fileHeaders || $fileHeaders[0] == 'HTTP/1.1 404 Not Found') {
              echo json_encode(array('exists'=>false));
              exit();
            }
          }
      }
      echo json_encode(array('exists'=>true));
   }

   public function CheckUpdateFileExists()
   {
     $config = ConfigSingleton::Instance();
     $versionUrl = $config->version_url.'/'.$config->version_file;
     $file = @file_get_contents($versionUrl);
     $exists = true;
     if($file === false)
     {
       $exists = false;
     }
     echo json_encode(array('exists'=>$exists,'url'=>$versionUrl));
   }

   public function ChooseBackupFile()
   {
    $config = ConfigSingleton::Instance();
    $updateFolder = realPath($config->update_folder);
    $exists = false;
    $files = glob("$updateFolder/backups/backup-*\.zip");
    $versions = array();
    foreach($files as $file)
    {
      preg_match("/backup-(\d+\.\d+\.\d+)\.zip/",$file,$matches);
      if(count($matches) > 0)
      {
        array_push($versions,$matches[1]);
      }
    }
    echo json_encode(array("versions"=>$versions));
   }

   public function ExecuteScripts()
   {
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $config = ConfigSingleton::Instance();
      $updateFolder = realPath($config->update_folder);
      $exists = true;
      if(!array_key_exists('scripts',$spyc))
      {
        $exists = false;
      }
      else
      {
        foreach($spyc['scripts'] as $script)
        {
          if(file_exists($updateFolder.'/'.$script))
          {
            include_once($updateFolder.'/'.$script);
          }
        }
      }
      echo json_encode(array());
   }

   public function InstallFiles()
   {
      $config = ConfigSingleton::Instance();
      $updateFiles = Spyc::YAMLLoad($this->GetUpdateFile());
      $updateFiles = $updateFiles['files'];
      $updateFolder = realpath($config->update_folder).'/';
      if(array_key_exists('add',$updateFiles))
      {
          $addFiles = $updateFiles['add'];
          for($i = 0; $i < count($addFiles); $i++)
          {
               $content = @file_get_contents($config->version_url."/".$addFiles[$i]['remote']);
               $pathInfo = pathinfo($updateFolder.$addFiles[$i]['local']);
               if(!file_exists($pathInfo['dirname']))
               {
                 // true = recursive
                 mkdir($pathInfo['dirname'],0755,true);
               }
               if(file_put_contents($updateFolder.$addFiles[$i]['local'],$content) === false)
               {
                 header('HTTP/1.0 500 '.Language::Instance()->server_error);
                 exit();
               }
          }
      }
      if(array_key_exists('delete',$updateFiles))
      {
          for($i = 0; $i < count($updateFiles['delete']); $i++)
          {
            if(file_exists($updateFolder.$updateFiles['delete'][$i]))
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
          }
      }

      echo json_encode(array());
   }

   public function RestoreBackup()
   {
      $config = ConfigSingleton::Instance();
      $version = $_GET['version'];
      $restoreTo = realpath($config->update_folder).'/';
      $zipFile = __DIR__."/backups/backup-$version.zip";
      $zip = new ZipArchive;
      if(!file_exists($zipFile))
      {
        echo json_encode(array("success"=>false));
      }
      else if($zip->open($zipFile) === true && $zip->extractTo($restoreTo))
      {
        file_put_contents("version.txt",$version);
        echo json_encode(array('success'=>true));
        unlink($zipFile);
      }
      else
      {
        echo json_encode(array('success'=>false));
      }
   }

   public function UpdateVersion()
   {
     $config = ConfigSingleton::Instance();
     $updateVersion = Spyc::YAMLLoad($this->GetUpdateFile())['version'];
     file_put_contents("version.txt",$updateVersion);
     echo json_encode(array());
   }

   public function VersionIsCurrent()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $currentVersion = explode(PHP_EOL,file_get_contents("version.txt"))[0];
      $updateVersion = $spyc['version'];
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
     $updateFolder = ConfigSingleton::Instance()->update_folder;
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
     // If there are no delete files we do not have to check for writability
     if(!array_key_exists('delete',$delete['files']))
     {
       return true;
     }
     $deleteFiles = $delete['files']['delete'];
     for($i = 0; $i < count($deleteFiles); $i++)
     {
       $file = $deleteFiles[$i];
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
     $updateFiles = $update['files']['add'];
     for($i = 0; $i < count($updateFiles); $i++)
     {
         $file = $updateFiles[$i]['local'];
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
