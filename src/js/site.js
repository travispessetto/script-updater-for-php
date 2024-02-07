var CONTROLLER = "controller.php";

$(document).ready(function()
{
		setInterval(waiting,1000);
		executeSteps(StepCounter.step);
		$(document).on('click','a',clearLinks);
		$(document).on('click','a[href="#"]',function(event)
		{
			event.preventDefault();
		});
});

var addUndoScripts = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['add_undo_scripts']));
	$.get(CONTROLLER,'action=AddUndoScripts').then(function(data)
	{
		clearWaiting();
		if(data.success)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['undo_scripts_added']));
			StepCounter.incrementAndExecuteStep(1);
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['add_undo_scripts_failed']));
		}
	},failed);
}

var advanceStep = function(step)
{
	return ++step;
}

var backupFiles = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['backup_files']));
	$.get(CONTROLLER,"action=BackupFiles").then(function(data)
	{
		clearWaiting();
		if(data.success)
		{
			  $("#info").append(sprintf('<div>{0}</div>',message['backup_success']));
				StepCounter.incrementStep(1);
				executeSteps(StepCounter.step);
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['backup_failed']));
		}
	}, failed);
}

var checkForBackups = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['check_for_backups']));
	$.get(CONTROLLER,"action=CheckForBackups").then(function(data)
	{
		clearWaiting();
		if(data.exists)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['backups_found']));
			$("#info").append(sprintf('<div>{0}</div>',message['prompt_update_backup']));
			$("#info").append(sprintf('<div><a href="#" class="primary" onclick="StepCounter.setStepAndExecute(Step.CheckVersionFileExists);">{0}</a>&nbsp;<a href="#" class="primary" onclick="StepCounter.setStepAndExecute(Step.ChooseBackupFile);" id="restoreVersion">{1}</a></div>',message['prompt_update_btn'],message['prompt_restore_btn']));
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['backups_not_found']));
			StepCounter.step = Step.CheckVersionFileExists;
			executeSteps(StepCounter.step);
		}
	},failed);
}

var checkForScripts = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['check_for_scripts']));
	$.get(CONTROLLER,"action=CheckForScripts").then(function(data)
	{
		clearWaiting();
		if(data.exists)
		{
			  $("#info").append(sprintf('<div>{0}</div>',message['scripts_exists']));
				StepCounter.incrementStep(1);
				executeSteps(StepCounter.step);
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['no_scripts_exists']));
			StepCounter.incrementStep(2);
			 executeSteps(StepCounter.step);
		}
	}, failed);
}

var checkIfUpdaterFilesBeingUpdated = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['check_if_updater_files_being_updated']));
	$.get(CONTROLLER,"action=CheckIfUpdaterIsBeingUpdated").then(function(data)
	{
		clearWaiting();
		if(data.update)
		{
			StepCounter.incrementAndExecuteStep(1);
		}
		else
		{
			StepCounter.incrementAndExecuteStep(2);
		}
	},failed);
}

var checkVersion = function()
{
	var current = false;
	$.get(CONTROLLER,"action=VersionIsCurrent").then(function(data)
	{
			clearWaiting();
		  current = data.current;
			if(current)
			{
				$("#info").append(sprintf('<div>'+message['version_up_to_date']+'</div>',data.current_version));
			}
			else
			{
				$("#info").append(sprintf('<div>'+message['version_out_of_date']+'</div>',data.current_version, data.update_version));
				StepCounter.incrementStep(1);
				$("#info").append(sprintf('<div><a href="#" class="primary" onclick="executeSteps(StepCounter.step);" id="updateVersion">{0}</a></div>',message['update_btn']));
			}
	},
	failed);
}

var checkVersionFileExists = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['check_version_file_exists']));
	$.get(CONTROLLER,'action=CheckUpdateFileExists').then(function(data)
	{
		clearWaiting();
		if(data.exists)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['update_file_exists']));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else
		{
			$("#info").append(sprintf(sprintf('<div>{0}</div>',message['update_file_does_not_exist']),data.url));
		}
	},failed);

}

var checkRemoteFiles = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['check_remote_files_exist']));
	$.get(CONTROLLER,"action=CheckRemoteFilesExist").then(function(data){
		if(data.exists)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['remote_files_exist']));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['remote_files_dont_exist']));
		}
	});
}

var checkWritablilty = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['check_files_are_writable']));
	$.get(CONTROLLER,"action=CheckFilesAreWritable").then(function(data)
	{
		 if(data.writable)
		 {
			 $("#info").append(sprintf('<div>{0}</div>',message['files_are_writable']));
			 StepCounter.incrementStep(1);
			 executeSteps(StepCounter.step);
		 }
		 else
		 {
		   $("#info").append(sprintf('<div>{0}</div>',message['files_are_not_writable']));
		 }
		 clearWaiting();
	},failed);
}

var chooseBackupFile = function()
{
	$("#info").append(sprintf('<div>{0} <span class=\"waiting\"></span>',message["fetching_backup_versions"]));
	$.get(CONTROLLER,'action=ChooseBackupFile').then(function(data)
	{
		clearWaiting();
		$("#info").append(sprintf('<div>{0}</div>',message['prompt_restore_version']));
		for(var i = 0; i < data.versions.length; ++i)
		{
			$("#info").append(sprintf('<div><a class="primary" onclick="findAllNewerBackupsAndRestoreRequestedBackup(\''+data.versions[i]+'\');" id="restoreBackup-{0}">{0}</a></div>',data.versions[i]));
		}

	},failed);
}

var clearLinks = function(event)
{
	$("#info a").parent("div").remove();
}

var clearWaiting = function()
{
		$(".waiting").removeClass("waiting");
}

var createAuxController = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></div>',message['create_aux_controller']));
	$.get(CONTROLLER,"action=CreateAuxController").then(function(data)
	{
		clearWaiting();
		$("#info").append(sprintf("<div>{0}</div>",message['aux_controllor_created']));
		CONTROLLER = "auxController.php";
		StepCounter.incrementAndExecuteStep(1);
	},failed);
}

var deleteAuxController = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['delete_aux_controller']));
	CONTROLLER = "controller.php";
	$.get(CONTROLLER,'action=DeleteAuxController').then(function(data)
	{
		clearWaiting();
		if(data.success)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['aux_controller_deleted']));
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['aux_controller_delete_failed']));
		}
		// It's not critical so continue even if not deleted
		StepCounter.incrementAndExecuteStep(1);
	},failed);
}

var executeScripts = function(afterVersionUpdate = false)
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['running_scripts']));
	$.get(CONTROLLER,"action=ExecuteScripts&afterVersionUpdate="+afterVersionUpdate).then(function(data)
	{

		$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['scripts_finished']));
		StepCounter.incrementStep(1);
		executeSteps(StepCounter.step);
		
	},failed);
}

var executeSteps = function(step)
{
	switch(step)
	{
		case Step.CheckForBackups:
			checkForBackups();
			break;
		case Step.BackupFiles:
			backupFiles();
			break;
		case Step.ChooseBackupFile:
			chooseBackupFile();
			break;
		case Step.CheckVersionFileExists:
			checkVersionFileExists();
			break;
		case Step.CheckVersion:
			checkVersion();
			break;
		case Step.CheckWritability:
			 checkWritablilty();
			 break;
		case Step.CheckRemoteFilesExist:
			checkRemoteFiles();
			break;
		case Step.CheckIfUpdaterFilesBeingUpdated:
			checkIfUpdaterFilesBeingUpdated();
			break;
		case Step.CreateAuxController:
			createAuxController();
			break;
		case Step.InstallFiles:
			installFiles();
			break;
	 	case Step.UpdateVersion:
			updateVersion();
			break;
		case Step.CheckForScripts:
			checkForScripts();
			break;
		case Step.AddUndoScripts:
			addUndoScripts();
			break;
		case Step.ExecuteScripts:
			executeScripts();
			break;
		case Step.DeleteAuxController:
			deleteAuxController();
			break;
		case Step.Finished:
			finished();
			break;
		case Step.ExecuteScriptsAfterVersionUpdate:
			executeScripts(true);
			break;
		default:
		  stepNotFound(step);
			break;
	}
}

var findAllNewerBackupsAndRestoreRequestedBackup = function(version)
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['find_newer_backups']));
	$.get(CONTROLLER,"action=FindAllNewerBackups&restoreVersion="+version).then(function(data)
	{
		clearWaiting();
		restoreBackup(data.restoreVersions);

	},failed);
	
}

var restoreBackup = function(versionArr)
{
	var version = versionArr.shift();
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',sprintf(message['restoring_backup'],version)));
	$.get(CONTROLLER,'action=restoreBackup&version='+version).then(
	function(data)
	{
		clearWaiting();
		$("#info").append(sprintf("<div id=\"restorationFinished\">{0}</div>",sprintf(message['version_restoration_finished'],version)));
		if(versionArr.length > 0)
		{
			restoreBackup(versionArr);
		}
	},failed);
}

var failed = function(xhr,status,error)
{
	clearWaiting();
	$("#info").append(sprintf('<div class="error"><b>{0}</b></div>',message['update_failed']));
	$("#info").append(sprintf('<div class="error"><b>Status:</b> {0}</div>',status));
	$("#info").append(sprintf('<div class="error"><b>Error:</b>  {0}</div>',error));

	try{
		var errorData = JSON.parse(xhr.responseText);
		$("#info").append(sprintf('<div class="error"><b>Message:</b>  {0}</div>',errorData.message));
		$("#info").append(sprintf('<div class="error"><b>Line:</b>  {0}</div>',errorData.line));
		$("#info").append(sprintf('<div class="error"><b>File:</b>  {0}</div>',errorData.file));
	}
	catch(e)
	{
		$("#info").append(printf('<div class="error"><b>Could not parse JSON:</b>  {0}</div>',e.message));
	}
}

var finished = function()
{
	$("#info").append(sprintf('<div id="updateFinished">{0}</div>',message['update_finished']));
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',sprintf(message['finish_button_waiting'])));
	$.get(CONTROLLER,"action=FinishButton").then(function(data)
	{
		clearWaiting();
		if(data.finishUrl)
		{
			$("#info").append(sprintf('<div><a href="{0}" class="primary">{1}</a></div>',data.url,message['finish_button_text']));
		}
		else
		{
			$("#info").append(sprintf('<div>{0}</div>',message['finish_button_not_found']));
		}

	},failed);
}

var installFiles = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['installing_files']));
	$.get(CONTROLLER,"action=InstallFiles").then(function(data){
			clearWaiting();
			$("#info").append(sprintf('<div>{0}</div>',message['files_installed']));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
	},failed);
}

var sprintf = function()
{
	 var message = arguments[0];
	 for(var i = 1; i < arguments.length; ++i)
	 {
		 message = message.replaceAll('{'+(i-1)+'}',arguments[i]);
	 }
	 return message;
}

var Step = {
	CheckForBackups:	0,
	BackupsExist: 1,
	ChooseBackupFile: 2,
	RestoreBackup: 3,
	RestorationComplete: 4,
	CheckVersionFileExists : 5,
	CheckVersion: 6,
	CheckWritability: 7,
	CheckRemoteFilesExist: 8,
	BackupFiles: 9,
	CheckIfUpdaterFilesBeingUpdated: 10,
	CreateAuxController: 11,
	InstallFiles: 12,
	CheckForScripts: 13,
	AddUndoScripts: 14,
	ExecuteScripts: 15,
	DeleteAuxController: 16,
	UpdateVersion: 17,
	ExecuteScriptsAfterVersionUpdate: 18,
	Finished: 19
}

var StepCounter = {
	 step: 0,
	 incrementStep: function(incBy)
	 {
		 this.step += incBy;
	 },

	 incrementAndExecuteStep: function(incBy)
	 {
		 this.step += incBy;
		 executeSteps(this.step);
	 },

	 setStepAndExecute: function(step)
	 {
		 this.step = step;
		 executeSteps(this.step);
	 }
}

var stepNotFound = function(step)
{
	$("#info").append(sprintf(sprintf('<div>{0}</div>',message['step_not_found']),step));
}

var updateVersion = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['updating_version']));
	$.get(CONTROLLER,"action=UpdateVersion").then(function(data){
			clearWaiting();
			$("#info").append(sprintf('<div>{0}</div>',message['version_file_updated']));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
	},failed);
}

var waiting = function()
{
		$(".waiting").each(function()
    {
    		var dots = $(this).text();
        dots += ".";
        if(dots.length > 3)
        {
          dots = "";
        }
        $(this).text(dots);
    });

}
