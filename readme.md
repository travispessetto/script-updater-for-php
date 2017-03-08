# PHP Updater

PHP Updater is an update script that can be used with any PHP project to update
the files via a couple clicks.  The script only requires that it can get at a
update file that contains the current version as well as the local filesystem
location and the remote location.  So, potentially Amazon S3 could be used.

## Screen Shots

[Imgur](http://i.imgur.com/kF1GrPp.png)
There is a new update

[Imgur](http://i.imgur.com/bcCyLJc.png)
The code is currently up to date

[Imgur](http://i.imgur.com/BbKhpls.png)
The update has finished

## Setup the update script

To set this up on the first line put the version number.  Currently it will
update if there is not a match but we suggest the version format of 0.0.0 as
it may verify these numbers are greater than the previous version later.

After you put the version on line 1 you can put each file that needs to be
replaced on the subsequent lines.  Start by typing where the file will be
on the local filesytem (the one being updated) and where to get it it on
the remote file system seperated by exactly 4 spaces between them.

The following is an example that will place the remote file files/foobar.txt
into foobar.txt:

```
1.0.3
foobar.txt    files/foobar.txt
```

## Setup the script

To set up the script you must edit config.php.  version_url is where you put the
base url for your update files.  version_file is the version file on the remote
host with the information to update.  update_folder is the folder on the local
host that you want to serve as your base.

## Changing the url for different versions

If you would like to have each version have its own update script you can make
sure you include the updaters config.php in the updates with the new location
for that version.

## License

Copyright (c) 2017 Travis Pessetto

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
