<?php
require_once(__DIR__."/configsingleton.php");
require_once(__DIR__."/langs/language.php");
require_once(__DIR__."/lib/spyc/Spyc.php");


class UpdateController
{

  public function __construct()
  {
      $plugins = scandir(__DIR__."/plugins");
      foreach($plugins as $plugin)
      {
        $plugin = "./plugins/$plugin";
		    $klass = ucfirst(pathinfo($plugin,PATHINFO_FILENAME));
        if(!is_dir($plugin) && !empty($klass))
        {
          require_once($plugin);
          $instance = new $klass();
          if(method_exists($instance,"ConstructorHook"))
          {
            $instance->ConstructorHook();
          }
        }
      }
  }

  public function __call($name,$args)
  {
      http_response_code(404);
      echo "No function $name exists";
  }

  public function AddUndoScripts()
  {
    $zip = new ZipArchive();
    if(!file_exists("version.txt"))
    {
      header("HTTP/1.0 404 ".Language::Instance()->file_not_found);
      echo Language::Instance()->file_not_found;
      exit();
    }
    $currentVersion = explode(PHP_EOL,file_get_contents("version.txt"))[0];
    $filename = __DIR__."/backups/backup-$currentVersion.zip";
    try
    {
      $config = ConfigSingleton::Instance();
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      if(!file_exists($filename))
      {
        unlink($filename);
        echo json_encode(array('success'=>false));
        exit();
      }
      if($zip->open($filename,ZipArchive::CREATE) !== true)
      {
        unlink($filename);
        echo json_encode(array('success'=>false));
        exit();
      }
      $restoreFile = $zip->getFromName('restore.yml');
      if(array_key_exists("scripts",$spyc) && array_key_exists("undo",$spyc['scripts']))
      {
        $restoreFile .= "scripts:".PHP_EOL;
        foreach($spyc['scripts']['undo'] as $script)
        {
          $name = $script['script'];
          $content = @file_get_contents($config->version_url."/".$script['remote']);
          $delete = $script['delete'] ? "true" : "false";
          $restoreFile .= "\t- {script: \"$name\", delete: $delete}".PHP_EOL;
          $zip->addFromString($name,$content);
        }
        $zip->addFromString('restore.yml',$restoreFile);
        $zip->close();
        echo json_encode(array('success'=>true));
        exit();
      }
      else
      {
        // NO scripts to add...success
        echo json_encode(array('success'=>true));
        exit();
      }
    }
    catch(Exception $ex)
    {
      unlink($filename);
      http_response_code(500);
      echo $ex->getMessage();
    }

  }

  public function BackupFiles()
  {
    $undoYaml = "delete:".PHP_EOL;
    $zip = new ZipArchive();
    if(!file_exists("version.txt"))
    {
      header("HTTP/1.0 404 ".Language::Instance()->file_not_found);
      echo Language::Instance()->file_not_found;
      exit();
    }
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
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $updateFiles = $spyc['files'];
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
            // File does not exist.  Therefore it maybe should be deleted when restored
            else 
            {
              if(!(array_key_exists("scripts",$spyc) && array_key_exists('do',$spyc['scripts'])
                && ($scriptIndex = array_search($addFiles[$i]['local'],array_column($spyc['scripts']['do'],'script'))) !== false
                && $spyc['scripts']['do'][$scriptIndex]['delete']))
              {
                $undoYaml .= "\t\t- ".$addFiles[$i]['local'].PHP_EOL;
              }
            }
          }
      }
      if(array_key_exists('delete',$updateFiles))
      {
        $deleteFiles = $updateFiles['delete'];
        for($i = 0; $i < count($deleteFiles); $i++)
        {
          if(file_exists($updateFolder.$deleteFiles[$i]))
          {
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
    }
    $zip->addFromString("restore.yml",$undoYaml);
    $zip->close();
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
     $files = $this->GetBackupArchives(); 
     $exists = false;
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
       foreach($spyc['scripts']['do'] as $script)
       {
         if(!file_exists($updateFolder.'/'.$script['script']))
         {
           $exists = false;
           break;
         }
       }
     }
     echo json_encode(array('exists'=>$exists));
   }

   public function CheckIfUpdaterIsBeingUpdated()
   {
     $spyc = Spyc::YAMLLoad(($this->GetUpdateFile()));
     $updateFiles = $spyc['files']['add'];
     $deleteFiles = $spyc['files']['delete'];
     $currentDir = __DIR__;
     foreach($updateFiles as $file)
     {
	   $localRealPath = realPath($file['local']);
       if($localRealPath !== false && strpos($currentDir,(string)$localRealPath) !== false)
       {
            echo json_encode(array("update"=>true));
            exit();
       }
     }
     foreach($deleteFiles as $file)
     {
		$fileRealPath = realPath($file);
        if($fileRealPath !== false && strpos($currentDir,(string)$fileRealPath) !== false)
        {
            echo json_encode(array("update"=>true));
            exit();
        }
     }
     echo json_encode(array("update"=>false));
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
    $exists = false;
    $files = $this->GetBackupArchives();
    $versions = array();
    foreach($files as $file)
    {
      preg_match("/backup-(\d+\.\d+\.\d+)\.zip/",$file,$matches);
      if(count($matches) > 0)
      {
        array_push($versions,$matches[1]);
      }
    }
    echo json_encode(array("versions"=>$versions,'backupfolder'=>$this->GetBackupFolder()));
   }

   public function CreateAuxController()
   {
      copy("controller.php","auxController.php");
      echo json_encode(array("success"=>true));
   }

   /// CAUTION: This function must always exist!
   public function DeleteAuxController()
   {
      if(file_exists("auxController.php"))
      {
        echo json_encode(array("success"=>unlink("auxController.php")));
      }
      else
      {
        echo json_encode(array("success"=>true));
      }
   }

   public function ExecuteScripts()
   {
      $spyc = Spyc::YAMLLoad($this->GetUpdateFile());
      $config = ConfigSingleton::Instance();
      $updateFolder = realPath($config->update_folder);
      $exists = true;
	  $scriptsRan = array();
	  $afterVersionUpdate = array_key_exists('afterVersionUpdate',$_GET) && filter_var($_GET['afterVersionUpdate'],FILTER_VALIDATE_BOOLEAN);
      if(!array_key_exists('scripts',$spyc) || !array_key_exists('do',$spyc['scripts']))
      {
        $exists = false;
      }
      else
      {
        foreach($spyc['scripts']['do'] as $script)
        {
		  $runNow = false;
		  if($afterVersionUpdate && array_key_exists('afterVersionUpdate',$script) && $script['afterVersionUpdate'])
		  {
			  $runNow = true;
		  }
		  else if($afterVersionUpdate
			  && (!array_key_exists('afterVersionUpdate',$script) || !$script['afterVersionUpdate']))
		  {
			  $runNow = true;
		  }

          if(file_exists($updateFolder.'/'.$script['script']) && $runNow)
          {
			$scriptsRan[] = $script;
            include_once($updateFolder.'/'.$script['script']);
            if($script['delete'])
            {
              unlink($updateFolder.'/'.$script['script']);
            }
          }
        }
      }
      echo json_encode(array('scriptsRan'=>$scriptsRan,'runAfterVersionUpdate'=>$_GET['afterVersionUpdate']));
   }

   public function FindAllNewerBackups()
   {
      $restoreVersion = $_GET['restoreVersion'];
      $config = ConfigSingleton::Instance();
      $updateFolder = realPath($config->update_folder);
	  $path = join(DIRECTORY_SEPARATOR,array("$updateFolder","backups","backup-*.zip"));
      $files = glob($path);
      $versions = array();
      foreach($files as $file)
      {
        preg_match("/backup-(\d+\.\d+\.\d+)\.zip/",$file,$matches);
        if(count($matches) > 0)
        {
          $version = $matches[1];
          if($this->IsNewerVersion($restoreVersion,$version))
          {
            $versions []= $version;
          }
        }
      }
	  $versions[] = $restoreVersion;
      usort($versions,array($this,"VersionOrganizerComparator"));
      echo json_encode(array("restoreVersions"=>$versions));

   }

   public function FinishButton()
   {
     $config = ConfigSingleton::Instance();
     $updateYaml = SpyC::YAMLLoad($this->GetUpdateFile());
     if(array_key_exists('finishUrl',$updateYaml))
     {
       echo json_encode(array('finishUrl'=>true,'url'=>$updateYaml['finishUrl']));
     }
     else
     {
       echo json_encode(array('finishUrl'=>false));
     }
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
      $zipFile = join(DIRECTORY_SEPARATOR,array(__DIR__,"backups","backup-$version.zip"));
      $zip = new ZipArchive;
      if(!file_exists($zipFile))
      {
        echo json_encode(array("success"=>false));
      }
      else if($zip->open($zipFile) === true && $zip->extractTo($restoreTo))
      {
        // delete all files from the yaml file if it exists
        $yamlFile = "$restoreTo/restore.yml";
        if(file_exists($yamlFile))
        {
          $spyc = Spyc::YAMLLoad($yamlFile);
          if(array_key_exists("delete",$spyc) && is_array($spyc['delete']))
          {
            foreach($spyc["delete"] as $file)
            {
              if(is_dir($file))
              {
                rmdir($file);
              }
              else
              {
                unlink($file);
              }
            }
          }
        }
        file_put_contents("version.txt",$version);
        echo json_encode(array('success'=>true));
		$zip->close();
        unlink($zipFile);
        unlink($yamlFile);
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

   private function VersionOrganizerComparator($a,$b)
   {
      if($a == $b)
      {
        return 0;
      }
      if($this->IsNewerVersion($a,$b))
      {
        return 1;
      }
      else
      {
        return -1;
      }
   }

   private function GetUpdateFile()
   {
      $config = ConfigSingleton::Instance();
      $versionUrl = $config->version_url.'/'.$config->version_file;
      $contents = file_get_contents($versionUrl);
      if($contents === false)
      {
        header("HTTP/1.0 404 ".Language::Instance()->file_not_found);
        echo Language::Instance()->file_not_found;
        exit();
      }
      else
      {
        return $contents;
      }
   }

   private function IsNewerVersion($currentVersion,$version)
   {
     if($currentVersion == $version)
     {
       return false;
     }
     $currentVersionSlices = explode('.',$currentVersion);
     $versionSlices = explode('.',$version);
    // if $versionSlices is shorter than $currentVersionSlices pad with zeros
    if(count($versionSlices) < count($currentVersionSlices))
    {
       $difference = count($currentVersionSlices) - count($versionSlices);
       for($i = 0; $i < $difference; ++$i)
       {
         $versionSlices[]= 0;
       }
    }
    else if(count($versionSlices) > count($currentVersionSlices))
    {
       $difference = count($versionSlices) - count($currentVersionSlices);
       for($i = 0; $i < $difference; ++$i)
       {
         $currentVersionSlices[]= 0;
       }
    }
    for($i = 0; $i < count($versionSlices); ++$i)
    {
       if((int)$versionSlices[$i] > (int)$currentVersionSlices[$i])
	   {
		 return true;
	   }
	   else if ((int)$versionSlices[$i] < (int)$currentVersionSlices[$i])
	   {
		 return false;
	   }
    }
    return false;
   }

   private function GetBackupArchives()
   {
      $backupFolder = $this->GetBackupFolder();
      return glob($backupFolder);
   }

   private function GetBackupFolder()
   {
      $config = ConfigSingleton::Instance();
      $updateFolder = realpath($config->update_folder);
      return join(DIRECTORY_SEPARATOR,array($updateFolder,"backups","backup-*.zip"));
   }


}
