<?php

echo "Creating update test!<br />";
if(file_exists("../update-test-source") && is_dir("../update-test-source"))
{
    rrmdir("../update-test-source");
}
if(file_exists("../update-test") && is_dir("../update-test"))
{
    rrmdir("../update-test");
}
echo "Generating update test folders<br />";
echo "Making update-test-source<br />";
mkdir('../update-test-source');
mkdir("../update-test-source/undo");
echo "Making test files<br />";
file_put_contents('../update-test-source/test1.txt',"This is a test file");
file_put_contents('../update-test-source/test2.txt',"This is a second test file");
file_put_contents('../update-test-source/test4.txt',"I should be here on update; removed on restore.");
echo "Making YAML test file<br />";
$yamlContents = <<<EOD
version:    1.0.0
files:
        add:
            - {local: "test1.txt", remote: "test1.txt"}
            - {local: "test2.txt", remote: "test2.txt"}
            - {local: "test4.txt", remote: "test4.txt"}
            - {local: "update-source-version.php", remote: "update-source-version.txt"}
        delete:
            - "test3.txt"
scripts:
    do:
        - {script: "update-source-version.php", delete: true, afterVersionUpdate: true}
    undo:
        - {script: "test-undo.php", remote: "undo/test-undo.txt", delete: true}
finishUrl: "./"
EOD;

echo "Making update script<br />";

$phpUpdateScript = <<<EOD
<?php
    \$content = file_get_contents(__DIR__.'/../update-test-source/update.yml');
    error_log("Content: \$content");
    \$version = preg_match("/1\\.0\\.(\\d+)/m",\$content,\$matches);
    \$incValue = \$matches[1] + 1;
    \$content = str_replace("1.0.\$matches[1]","1.0.\$incValue",\$content);
    error_log("New content: \$content");
    file_put_contents('../update-test-source/update.yml',\$content);
EOD;

$testUndo = <<<EOD
<?php
    file_put_contents("undo-test.txt","This file should exist on an undo");
EOD;

file_put_contents("../update-test-source/undo/test-undo.txt",$testUndo);
file_put_contents("../update-test-source/update-source-version.txt",$phpUpdateScript);
file_put_contents('../update-test-source/update.yml',$yamlContents); 

echo "Making update-test folder<br />";
mkdir('../update-test');
file_put_contents("../update-test/version.txt","0.0.0");

echo "Copying in the updater files<br />";

copy_directory('../src/css',"../update-test/css");
copy_directory("../src/js","../update-test/js");
copy_directory("../src/langs","../update-test/langs");
copy_directory("../src/lib","../update-test/lib");

copy("../src/configsingleton.php","../update-test/configsingleton.php");
copy("../src/controller.php","../update-test/controller.php");
copy('../src/updateController.php','../update-test/updateController.php');
copy("../src/index.php","../update-test/index.php");
copy_directory("../plugins","../update-test/plugins");
$configContents = <<<EOD
<?php
\$config['version_url'] = "{url}";
\$config['version_file'] = "update.yml";
\$config["update_folder"] = "./";
EOD;

$linkUrl =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$linkUrl = explode('/', $linkUrl);
array_pop($linkUrl);
array_pop($linkUrl);
$linkUrl = implode('/', $linkUrl);
$linkUrl .= "/update-test-source";

$configContents = str_replace("{url}",$linkUrl,$configContents);

echo "Creating config file <br />";
file_put_contents("../update-test/config.php",$configContents);

echo "Creating text files <br />";
file_put_contents('../update-test/test1.txt',"If this is unchanged it's bad");
file_put_contents('../update-test/test2.txt',"Nobody changed me ");
file_put_contents('../update-test/test3.txt',"I should be deleted");

function copy_directory($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				copy_directory($src .'/'. $file, $dst .'/'. $file);
			}
			else {
				copy($src .'/'. $file,$dst .'/'. $file);
			}
		}
	}
	closedir($dir);
}

function rrmdir($src) {
    echo "Removing DIR: $src<br />";
    $src = realpath($src);
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                echo "Removing FILE: $full<br />";
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}