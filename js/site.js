$(document).ready(function()
{
		setInterval(waiting,1000);
		executeSteps(0);
});

var advanceStep = function(step)
{
	return ++step;
}

var checkVersion = function()
{
	var current = false;
	$.get("controller.php","action=VersionIsCurrent").then(function(data)
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
				$("#info").append(sprintf('<div><a href="#" class="primary" onclick="executeSteps(2);">{0}</a></div>',message['update_btn']));
			}
	},
	failed);
}

var checkVersionFileExists = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['check_version_file_exists']));
	$.get('controller.php','action=CheckUpdateFileExists').then(function(data)
	{
		clearWaiting();
		if(data.exists)
		{
			$("#info").append(sprintf('<div>{0}</div>',message['update_file_exists']));
			executeSteps(1);
		}
		else
		{
			$("#info").append(sprintf(sprintf('<div>{0}</div>',message['update_file_does_not_exist']),data.url));
		}
	},failed);

}

var checkWritablilty = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>',message['check_files_are_writable']));
	$.get("controller.php","action=CheckFilesAreWritable").then(function(data)
	{
		 if(data.writable)
		 {
			 $("#info").append(sprintf('<div>{0}</div>',message['files_are_writable']));
			 executeSteps(3);
		 }
		 else
		 {
		   $("#info").append(sprintf('<div>{0}</div>',message['files_are_not_writable']));
		 }
		 clearWaiting();
	},failed);
}

var clearLinks = function()
{
	$("a").remove();
}

var clearWaiting = function()
{
		$(".waiting").removeClass("waiting");
}


var executeNextStep = function()
{

}

var executeSteps = function(step)
{
	switch(step)
	{
		case 0:
			checkVersionFileExists();
			break;
		case 1:
			checkVersion();
			break;
		case 2:
			 clearLinks();
			 checkWritablilty();
			 break;
		case 3:
			installFiles();
			break;
	 case 4:
			updateVersion();
			break;
		case 5:
			finished();
			break;
	}
}

var failed = function(xhr,status,error)
{
	clearWaiting();
	$("#info").append(sprintf('<div>{0}</div>',message['update_failed']));
	$("#info").append(sprintf('<div>Status: {0}</div>',status));
	$("#info").append(sprintf('<div>Error:  {0}</div>',error));
}

var finished = function()
{
	$("#info").append(sprintf('<div>{0}</div>',message['update_finished']));
}

var installFiles = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['installing_files']));
	$.get("controller.php","action=InstallFiles").then(function(data){
			clearWaiting();
			$("#info").append(sprintf('<div>{0}</div>',message['files_installed']));
			executeSteps(4);
	},failed);
}

var sprintf = function()
{
	 var message = arguments[0];
	 for(var i = 1; i < arguments.length; ++i)
	 {
		 message = message.replace('{'+(i-1)+'}',arguments[i]);
	 }
	 return message;
}

var updateVersion = function()
{
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>',message['updating_version']));
	$.get("controller.php","action=UpdateVersion").then(function(data){
			clearWaiting();
			$("#info").append(sprintf('<div>{0}</div>',message['version_file_updated']));
			executeSteps(5);
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
