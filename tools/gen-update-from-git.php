<?php

// tool to show changed files. Will store in lastcommit.txt
$commitFile = "lastcommit.txt";

exec('git rev-list HEAD',$output,$retval);
$latestCommit = $output[0];

echo "The latest commit is: $latestCommit".PHP_EOL;

$lastCommit = '';
if(!file_exists($commitFile))
{
    echo "There is no last commit file.  Will start from beginning".PHP_EOL;
    $lastCommit = end($output);
}
else
{
    $lastCommit = trim(file_get_contents($commitFile));
}

echo "Getting files modified from $lastCommit to $latestCommit".PHP_EOL;
// clear output array
$output = [];
$cmd = "git diff --name-status $lastCommit $latestCommit";
echo "Running $cmd".PHP_EOL;

exec($cmd,$output,$retval);

foreach($output as $line)
{
    $lineFragments = explode("\t",$line);
    if($lineFragments[0] == 'A')
    {
        echo "Adding $lineFragments[1]".PHP_EOL;
    }
    else if($lineFragments[0] == 'D')
    {
        echo "Deleting $lineFragments[1]".PHP_EOL;
    }
    else if(str_starts_with($lineFragments[0],'R'))
    {
        echo "A file is renamed from $lineFragments[0] to $lineFragments[1]";
    }
}