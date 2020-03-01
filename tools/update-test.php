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
        delete:
            - "test3.txt"
scripts:
    - "scripts/writefile.php"
EOD;
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
file_put_contents('../update-test/test2.txt',"I should be deleted");

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