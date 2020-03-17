<?php

echo "Generating update test folders<br />";
echo "Making update-test-source<br />";
mkdir('../update-test-source');
echo "Making test files<br />";
file_put_contents('../update-test-source/test1.txt',"This is a test file");
file_put_contents('../update-test-source/test2.txt',"This is a second test file");
echo "Making YAML test file<br />";
$yamlContents = <<<EOD
version:    1.0.0
files:
        add:
            - {local: "test1.txt", remote: "test1.txt"}
            - {local: "test2.txt", remote: "test2.txt"}
            - {local: "update-source-version.php", remote: "update-source-version.txt"}
        delete:
            - "test3.txt"
scripts:
    do:
        - {script: "update-source-version.php", delete: true}
    undo:
        - {script: "test-undo.php", remote: "undo/test-undo.txt", delete: true}
EOD;
echo "Making update script<br />";
$phpUpdateScript = <<<EOD
<?php
    \$content = file_get_contents(__DIR__.'/../update-test-source/update.yml');
    error_log("Content: \$content");
    \$version = preg_match("/1\\.0\\.(\\d+)/m",\$content,\$matches);
    \$incValue = \$matches[1] + 1;
    \$content = str_replace("1.0.\$matches[1]","\$incValue.0.\$incValue",\$content);
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
copy_directory('../css',"../update-test/css");
copy_directory("../js","../update-test/js");
copy_directory("../langs","../update-test/langs");
copy_directory("../lib","../update-test/lib");
copy("../configsingleton.php","../update-test/configsingleton.php");
copy("../controller.php","../update-test/controller.php");
copy("../index.php","../update-test/index.php");
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

file_put_contents("../update-test/config.php",$configContents);

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