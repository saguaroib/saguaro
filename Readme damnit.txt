This will help explain things a little bit while you set up your new image board.

=========================================
====A list of files and what they are====
=========================================
-config.php       [This file holds your configuration, you must change the settings!]
-strings_e.php    [This file is the english language file, only neccesary to edit for cutomization.]
-strings_es.php   [This file is the spanish language file, only neccesary to edit for cutomization.]
-futaba.css       [This file determines the look of your board. {the default style}]
-monotone.css     [This file determines the look of your board. {created by spoot for you}]
-burichan.css     [This file determines the look of your board. {a converted style}]
-kusaba.css       [This file determines the look of your board. {another style}]
-img.css	  [This file makes the thumbnails in the admin panel work.]
-imgboard.php     [This file is essentially the brain of the board, {This is your brain on drugs.}]
-gif2png          Creates the static thumbnail for animated (GIF) images.
-php_captcha.php  [This creates captcha images]
-img.jpg	  [This is used by php_captcha.php]
-styleswitch.js	  [This is the javascript that adds the stylesheet switcher]

-src/             			[This holds your images]
--empty           			{empty}
-thumb/          			[This holds your images(in thumbnail form!)]
--empty          		 	{empty}
-filters/         			[This folder holds files which determine your word filters and tripcode filters.]
--trip.php       			[This file holds capcodes.]      
--trip.php2       			[This file determines what tripcodes get filtered into what.]     
--word.php        			[This file determines what words get filtered into what.]
--bbcode.php	 			[This file converts bbcode.]
-jquery/	  			[RePod's Jquery suite, not gonna bother listing the ever-shifting contents of this.]
--repod_jquery_suite_README.txt 		[A friendly explanation of RePod's jquery suite, by the man himself.]

==========================
=Basic Installation Guide=
==========================
This guide assumes you have a host already and know how to upload it and make MySQL databases. If you do not know how to do this, please do not ask me.
I'm all for helping people, but there's a certain point where you need to learn on your own. Google is your friend here.

You'll need to edit config.php in a text editor. Don't even think of using notepad because chances are it won't show the formatting and it'll look like a huge chunk of text, which is bad. I recommend notepad ++ for windows, and SciTE for linux, which are what I use. Anyway, once you have the config file open, the settings are pretty self explanatory, just go line by line and set everything to how you want it/need it. The actual "setting" part, where you specify the option you want will be between two ' marks. Also if you're entering something in like a sentence in some of the settings, and you want to use a ' mark like in "can't", type it like "can\'t" or else your setup won't work.

After you have everything set up, upload everything to your server and change the permissions on the following folders to 777(have all 9 boxes checked):
whatever_folder_you're_putting_everything_in/
src/
thumb/
Then go in your browser and open imgboard.php(the one on your server, not your computer). If you have everything set up right, it'll generate an index.html and you can post.

=================
=The Board Links=
=================
I figured I'd say it here:
No, the board links are not automatically generated, you need to make it yourself.
This requires basic HTML knowledge, which if you plan on running a website you should learn.
If you just want a quick list of links, here's an example:
For a list like this:
[a / b / c]
You need to enter this into S_BOARDLIST:
[<a href="/a/">a</a> / <a href="/b/">b</a> / <a href="/c/">c</a>]
This assumes your boards are directly after the root, IE: mysite.com/b/, not mysite.com/saguaro/b/

If you want any more help than this with making the board links, Google 'HTML Tutorial'

========================
====Bare Necessities====
========================
+PHP 5.x.x installed
+MySQL Version 4.0 or higher

============
====Tips====
============
+you can tweak your board's appearance by editing the .css file and the strings_e.php file
+don't screw around in imageboard.php unless you know what you're doing.
+make sure the permissions for src/ and thumb/ are set to 777
+the css files saguaro uses have some additions as compared to default futallaby/futaba css files, so using an old file may make small bits look wonky
+to determine what file types you want to be accepted, go into imgboard.php hit control+f and type in "file types",
then uncomment (remove the "//") or comment (add "//") the types you want/don't want.
default is gif, jpg, png, however available file types are:
jpg, png, gif, swf, psd, bmp, tiff, jpc, jp2, jpx, jb2, swc, iff, wbmp, and xbm. (I don't even know what some of those are)
+have fun!
+if you still need help check the wiki: http://saguaroimgboard.co.cc/wiki/

=========================
====Making new boards====
=========================
Since I often get asked about this, I'll make a section on it.
In order to make a new board after already setting one up, just make a copy of the directory 
and rename it to whatever you want the new directory to be.
Say you set up /a/ first and now want a /b/ as well, make a new directory called /b/ and copy /a/'s contents to /b/.
Then edit /b/'s config.php and change the settings. 
>>Just make sure that no two boards share the same value for SQLLOG (Line 3)<<

==============
====bbcode====
==============
From 0.978 onward there is a bbcode feature thanks to Glas.
This means that now you can use the following: 
(These are working examples to give you an idea of how to use them.)
[b]bold[/b]
[i]italicized[/i]
[u]underlined[/u]
[spoiler]spoiler[/spoiler]
[color=red]colors[/color]
[s]slashed[/s]
[size=1]resized (use 1-7)[/size]
[quote]quote[/quote]
and my favorite:
[youtube]http://www.youtube.com/watch?v=fgtxb9yBggc[/youtube]
Thanks again!


==================
====Contact Me====
==================
To ask for my indispensable wisdom send me an email at 

spoot@saguaroimgboard.co.cc

Send flames, questions, and comments.