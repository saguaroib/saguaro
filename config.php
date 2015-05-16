<?php

//Essentials - You NEED to change these, or at least make sure they're good
define(SQLDB, 'CHANGEME');									//Database used by image board
define(SQLHOST, 'CHANGEME');							//MySQL server address, usually localhost
define(SQLLOG, 'CHANGEME');									//Table (NOT DATABASE) used by image board
define(SQLBANLOG, 'CHANEGME');                                   // Table (NOT DATABASE) that holds ban records
define(SQLUSER, 'CHANGEME');									//MySQL user (must be changed)
define(SQLPASS, 'CHANGEME');							//MySQL user's password (must be changed)
define(BOARD_DIR, 'CHANGEME');                               //Folder name of board, EX: /ba/ would be ba
define(PANEL_PASS, 'CHANGEME');							//Janitor password  (CHANGE THIS YO)

//Basic settings
define(TITLE, 'Saguaro Imageboard');				//Name of this image board
define(S_HEADSUB, 'Fresh outta the .zip!');  			//subtitle underneath title
define(SHOWTITLETXT, '1');								//Show TITLE at top (1: yes  0: no)
define(SHOWTITLEIMG, '0');								//Show image at top (0: no, 1: single, 2: rotating)
define(TITLEIMG, '');									//Title image (point to php file if rotating)
define(LANGUAGE, 'en-us');								//Language file to use from "lang" folder.
define(DATE_FORMAT, 'm/d/y');                      //Formatting for the date in each post, see http://php.net/manual/en/function.date.php for different options

//From here down all these settings are optional.

//Extra settings - No need to change these for a basic installation, but you may want these options
define(MAX_KB, '2048');									//Maximum upload size in KB
define(MAX_W,  '250');									//OP images exceeding this width will be thumbnailed
define(MAX_H,  '250');									//OP images exceeding this height will be thumbnailed
define(MAXR_W,  '125');									//Image replies exceeding this width will be thumbnailed
define(MAXR_H,  '125');									//Image replies exceeding this height will be thumbnailed
define(MIN_W, '30');									//minimum image dimensions - width
define(MIN_H, '30');									//minimum image dimensions - height
define(PAGE_DEF, '10');									//Images per page
define(RENZOKU, '10');									//Seconds between posts (floodcheck)
define(RENZOKU2, '13');									//Seconds between image posts (floodcheck)
define(S_POSTLENGTH, '3000'); 							//Maximum character length of posts
define(MAX_RES, '500');									//Maximum topic bumps
define(USE_THUMB, 1);									//Use thumbnails (1: yes  0: no)
define(PROXY_CHECK, 0);									//Enable proxy check (1: yes  0: no)
define(DISP_ID, 0);										//Display user IDs (1: yes  0: no)
define(BR_CHECK, 0);									//Max lines per post (0 = no limit)
define(TRIPKEY, '!');									//this character is displayed before tripcodes
define(MANTHUMBS, '1');									//Display thumbnails in manager panel- you may want it off if you have too many images (1: yes  0: no)
define(BOTCHECK, '0');									//Use CAPTCHAs
define(USE_BBCODE, '1');								//Use BBcode
define(S_SAGE, 'sage');									//What to change sage to

//RePod's JS suite
define(USE_JS_SETTINGS, '1');							//Include the JS suite's settings - enables user side settings
define(USE_IMG_HOVER, '1');							//Use image expansion on hover
define(USE_IMG_TOOLBAR, '1');							//Use the image search toolbars
define(USE_IMG_EXP, '1');							//Use image expansion
define(USE_UTIL_QUOTE, '1');							//Use utility quotes
define(USE_INF_SCROLL, '0');							//Use infinite scroll
define(USE_FORCE_WRAP, '1');							//Use forced post wrapping
define(USE_UPDATER, '1');							//Use thread updater
define(USE_THREAD_STATS, '1'); 							//Use thread stats
define(USE_EXTRAS, '1');       	 						//Automatically include all .js files in JS_PATH/extra/



//CSS stuff.
//These are required, but you can change them.
//TO-DO: Make scalable. - RePod
define(CSSFILE, 'css/saguaba.css');							//location of the css file, also the default
define(STYLESHEET_1, 'Saguaba');							//Name of the first stylesheet.
define(CSSFILE2, 'css/sagurichan.css');						//location of the second stylesheet.
define(STYLESHEET_2, 'Sagurichan');						//Name of the second stylesheet.
define(CSSFILE3, 'css/futaba.css');						//location of the third stylesheet.
define(STYLESHEET_3, 'Futaba');						//Name of the third stylesheet.
define(CSSFILE4, 'css/burichan.css');						//location of the fourth stylesheet.
define(STYLESHEET_4, 'Burichan');						//Name of the fourth stylesheet.
/*define(CSSFILE3, 'css/kusaba.css');						//location of the third stylesheet.
define(STYLESHEET_3, 'Kusaba');						//Name of the third stylesheet.
define(CSSFILE4, 'css/monotone.css');						//location of the fourth stylesheet.
define(STYLESHEET_4, 'Monotone');						//Name of the fourth stylesheet.*/


//Capcodes - show 'em who's boss (put it as your trip. IE: "name#CHANGEME" would result as "name## Admin ##!09EKYZv3TU")
define("ADMIN_PASS", 'CHANGEME');     	   						//admin pass
define("ACAPCODE", ' <font color="FF101A"> ## Admin ## </font>'); //admin capcode
define("MOD_PASS", 'CHANGEMEPLZ');     	   						//Mod pass
define("MCAPCODE", ' <font color="770099"> ## Mod ## </font>'); 	//mod capcode



//Advanced Settings
define(IMG_DIR, 'src/');								//Image directory (needs to be 777)
define(THUMB_DIR,'thumb/');								//Thumbnail directory (needs to be 777)
define(HOME,  '/');										//Site home directory (up one level by default
define(LOG_MAX,  '1500');								//Maxium number of entries
define(PHP_SELF, 'imgboard.php');						//Name of main script file
define(PHP_SELF2, 'index.html');						//Name of main htm file
define(PHP_EXT, '.html');								//Extension used for board pages after first
define(JS_PATH, 'plugins/jquery'); 								//relative path from imgboard.php of the jquery folder without a trailing slash
define(PLUG_PATH, 'plugins');                              //Plugins folder path without the trailing slash

//Even more settings - there can never be enough
define(S_OMITT_NUM, '5');								//number of posts to display in each thread on the index
define(NOPICBOX, '0');									//whether or not to have the [No Picture] checkbox (1: yes  0: no)
define(DUPE_CHECK, '0');								//whether or not to check for duplicate images
define(S_BOARDLIST, '[a / b / c] | [d / e / f]');    //meta description for this board (LOOK AT THE README)
define(S_DESCR, 'An imageboard powered by saguaro');    //meta description for this board
define(EXTRA_SHIT, '');         //Any extra javascripts you want to include inside the <head>

//Advertisements
define(USE_ADS1, 0);		//Use advertisements (top) (1: yes  0: no)
define(ADS1, '<center>ads ads ads</center>');		//advertisement code (top)

define(USE_ADS2, 0);		//Use advertisements (below post form) (1: yes  0: no)
define(ADS2, '<center>ads ads ads</center>');		//advertisement code (below post form)

define(USE_ADS3, 0);		//Use advertisements (bottom) (1: yes  0: no)
define(ADS3, '<center>ads ads ads</center>');		//advertisement code (bottom)			

//BEWARE: Debug mode can display sensitive data that could be exploited. Use with caution
define(DEBUG_MODE, 0);                              //0: off, 1: on. Enabling this will display any SQL errors as well as making redirects between posting/log updates slower.

?>