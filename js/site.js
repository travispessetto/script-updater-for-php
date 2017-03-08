$(document).ready(function()
{
		setInterval(waiting,1000);
		executeSteps(1);
});


var checkVersion = function()
{
	var current = false;
	$.get("controller","action=VersionIsCurrent").then(function(data)
	{
			clearWaiting();
		  current = data.current;
			if(current)
			{
				$("#info").append("<div>Version is up to date at version "+data.version+'</div>');
			}
			else
			{
				$("#info").append('<div>Version is out of date</div><div>Latest version is ' + data.version +'</div>');
				$("#info").append('<div><a href="#" class="primary" onclick="executeSteps(2);">Update Now</a></div>')
			}
	},
	failed);
}

var clearLinks = function()
{
	$("a").remove();
}

var clearWaiting = function()
{
		$(".waiting").removeClass("waiting");
}


var executeSteps = function(step)
{
	console.log("Execute step: " + step);
	switch(step)
	{
		case 1:
			checkVersion();
			break;
		case 2:
			clearLinks();
			installFiles();
			break;
	 case 3:
			updateVersion();
			break;
		case 4:
			finished();
			break;
	}
}

var failed = function(xhr,status,error)
{
	clearWaiting();
	$("#info").append('<div>The update failed</div>')
	$("#info").append(status);
}

var finished = function()
{
	$("#info").append('<div>Update finished</div>');
}

var installFiles = function()
{
	$("#info").append('<div>Downloading and installing files <span class="waiting"></span></div>');
	$.get("controller","action=InstallFiles").then(function(data){
			clearWaiting();
			$("#info").append('<div>Files installed</div>');
			executeSteps(3);
	},failed);
}

var updateVersion = function()
{
	$("#info").append('<div>Updating the version file <span class="waiting"></span></div>');
	$.get("controller","action=UpdateVersion").then(function(data){
			clearWaiting();
			$("#info").append('<div>Version files updated</div>');
			executeSteps(4);
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
