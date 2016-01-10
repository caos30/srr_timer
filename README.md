# [srr_timer](https://github.com/caos30/srr_timer)

## Description

A simple PHP + jQuery tool for control the time you spent on your daily work tasks. It uses SQLite for database, so it's very easy to install and to backup it. 
It show you statistics (daily, monthly and yearly) about the time spent on each task. It let to hide unactive tasks. And it has a littel cool tool for ring a chimp (bell) each X minutes or X hours, 
so in this way you can force to you to make a pause in your intensive work each once per hour, for example, or simply use it as an alarm.

The interface is translated to several languages: 

 - english
 - catalan 
 - castillian (spanish of spain)

You're welcome to add another one, there are only 50 brief sentences ;)

## Screenshot

![screenshot](/screenshot.png?raw=true "Main panel")

## Demo

http://imasdeweb.com/opensource/demos/srr_timer/index.htm

## Installation & requeriments

As simple as to create a folder in your server and to put inside these files.

The server need to have the SQLite PDO installed (it's usually installed by default in the most of cases).

## SQLite database

- this project use my own class_aSQLite.php (i'm close to upload it on GitHub) class for manage SQLite
- you can edit raw SQLite database donlowading it using FTP
- but you can edit it using this URL /lib/db/admin which use the same class_aSQLite.php library (recommended)
- the default login credentials to access this panel are the typical: admin/1234, so you should cahnge it at /data/admin/admin.php using FTP
- you shouldn't need to have raw access to the database... and it's not recommended to do it, but if you decide to work on this project as developer you will need it ;)

## Security

By now there is no any secure login system, so it's recommendable to protect that folder using the Apache password folder protection, for example.

## How to use this tool

Although functions of the tool are very simple and the interface enough simplified and clear, let me expose the main guidelines:

1. you add a new "task" and from there you has a new timer to be used

2. start/stop the timer of a task using its "play"/"stop" icons (triangle/square)

3. you can only have one task timing at the same time

4. when the system detect that it's a new day then save times and automatically change the "current day" or "today date"

5. for each task you have these operand buttons:

- play/start, in the middle of the row
- stats, showing daily/monthly/yearly stats
- edit, let to change task name and active/unactive task
- delete, it remove completely any data of the task in database
- merge, it pass the times of this task to another existing task, day by day, afterwards removing this one
- increase/decrease, a serie of numeric buttons which let you sum or substract X minutes to the "today" timer
- the "zero" numeric button make a RESET of the "today" timer for this task

6. at the bottom of the page you has a nice tool: the repetitive chime (bell). You specify -for example- "1 hour" and then press "start", and a counter will began a countdown of one hour duration, and after then the countdown will began again.

## To do

I developed this tool because i didn't find any other tool like it: simple, easy, without complicated MySQL installation, multi-device (it run in ANY BROWSER in "ANY DEVICE"!), not local but cloud, etc.

But i have a list of pending features to implement, so you're welcome ;)

- add to the configuration dialog a way to adjust the hours that define a new day (now by default is setted to 05:00am)
- multi-user and login system
- add a way to edit the times on other days than today. It can be done only accessing to the database.
- a new HTML design really RESPONSIVE ;)
- more graphs? for example: graph suming all hours by day of all tasks
- average stats ? by week, month, year...
- automatic saving on server each minute (actually it only save task progress when you click on "stop" !)
- once i have a FireFox OS phone, i would like to make a FireFox OS app from it :)
- more interface/style options (themes?)
- ability to color code each task (tagging)
- pie charts at the stats dialog? 
- ability to create subprojects
- send some kind of report by email?
- export to csv 
- when adding a new task offer list of common tasks (sport,self-care, sleep, transport, work, eat, read, shopping, entertainment, housework, cinema, walk, study, internet, drink, party, etc...)


## License

LICENSE: GPL v2

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

## Team

As developers & translators: 

 - Sergi Rodrigues (from Novembre 2009)

## Versions Log

== 1.0 [18-11-2009]

 - first version.

== 2.0 [19-06-2014]

 - upgraded the database engine to php_aSQLite.

== 2.1 [23-11-2015]

 - multiple improvements in style: replaced text by icons in most of buttons, enlighted the tasks used today
 - added column "days" (at right) in the main view (task list)
 - added the multi-language feature
 - on the stats popup of a task added the button "years"
 - added a new feature for tasks: active/unactive, for don't show at task list the not active tasks
 - uploaded to GitHub :)

== 2.2 [???]

 - moved the sqlite database to the folder /data for make easier the updating of the app without affect the 'local data'
 - added the number of version at the 'settings' dialog, which it's stored at /version.txt file by developers
 - refactoring of the database layer

More details at: https://github.com/caos30/srr_timer/commits/master
