# Updater for PHP

Updater for PHP is an update script that can be used with any PHP project to update
the files via a couple clicks.  The script only requires that it can get at a
update file that contains the current version as well as the local filesystem
location and the remote location.  So, potentially Amazon S3 could be used.

Did we mention that this update script can adapt to languages?  All you need is
the appropriate language files in js/langs and langs/php.  There are currently
two languages supported out of the box, English and Spanish.  The script will
default to English if it cannot find a suitable language file based on the
brower's settings.

## Screen Shots

![Imgur](http://i.imgur.com/kF1GrPp.png)

There is a new update

![Imgur](http://i.imgur.com/bcCyLJc.png)

The code is currently up to date

![Imgur](http://i.imgur.com/BbKhpls.png)

The update has finished

## Setup the Update Script

To set this up on the first line put the version number.  To specify the version
number you put a series of integers seperated by periods.  The numbers are more
significant going from right to left with the leftmost integer being the most
significant.  An example version is 1.0.0.  1.0.0.1 would trigger an update on
1.0.0 and 2.0 would trigger an update on 1.0.0.1.

After you put the version on line 1 you can put each file that needs to be
replaced on the subsequent lines.  Start by typing where the file will be
on the local filesytem (the one being updated) and where to get it it on
the remote file system seperated by exactly 4 spaces between them.

Currently there is no delete or script running capabilities.  We hope to add
these in the future.

The following is an example that will place the remote file files/foobar.txt
into foobar.txt:

```
1.0.3
foobar.txt    files/foobar.txt
```

## Setup the Script

To set up the script you must edit config.php.  version_url is where you put the
base url for your update files.  version_file is the version file on the remote
host with the information to update.  update_folder is the folder on the local
host that you want to serve as your base.

## Changing the URL for Different Versions

If you would like to have each version have its own update script you can make
sure you include the updaters config.php in the updates with the new location
for that version.

## Current Failsafes

There is currently only one failsafe for the update.  That is it checks for
file writability before it trys to install the files.  This is to prevent an
update from being only partially completed and, as a result, having your scripts
not work right.

In the future we hope to implement a rollback system that can undo the changes
if the update fails when installing files.

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
