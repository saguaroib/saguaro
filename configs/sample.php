<?php
/*
    Before proceeding, it is recommend you work on a copy of this file.
 
    After making the desired changes, save that file into the same directory
    as this sample config.

    To switch between configs, modify config.php in the parent directory to
    include your config's file name (excluding extension).
*/

//Essentials - You NEED to change these, or at least make sure they're good
define(SQLDB, 'CHANGEME'); //Database used by image board
define(SQLHOST, 'CHANGEME'); //MySQL server address, usually localhost
define(SQLLOG, 'CHANGEME'); //Table (NOT DATABASE) used by image board
define(SQLBANLOG, 'CHANGEME'); // Table (NOT DATABASE) that holds ban records
define(SQLMODSLOG, 'CHANGEME');
define(SQLDELLOG, 'CHANGEME');
define(SQLUSER, 'CHANGEME'); //MySQL user (must be changed)
define(SQLPASS, 'CHANGEME'); //MySQL user's password (must be changed)
define(BOARD_DIR, 'CHANGEME'); //Folder name of board, EX: /ba/ would be ba
//This is the salt string used to generate secure tripcodes. Example: AASGewgw34ESRVfJ65hweragw43hqerZD
define(PANEL_PASS, 'CHANGEME'); //Janitor password  (CHANGE THIS YO)
define(PHP_SELF_ABS, 'CHANGEME'); // Absolute path from the site to the imgboard.php, ex: http://yoursite.com/boardDir/imgboard.php
define(PHP_SELF2_ABS, 'CHANGEME');  // Absolute path from the site to the INDEX.html, ex: http://yoursite.com/boardDir/index.html
define(DATA_SERVER, 'CHANGEME'); //Your site's root html path, WITH a trailing slash, ex: http://yoursite.com/
define(CSS_PATH, 'CHANGEME'); //absolute html path to the css folder with the trailing slash
define(SITE_ROOT, 'CHANGEME'); //simplified site domain ONLY, EX: saguaro.org
define(SITE_SUFFIX, 'CHANGEME'); //Domain suffix, ex: org, com, info, net. NO DOTS, ONLY LETTERS
define(JS_PATH, 'CHANGEME');                          //relative path from imgboard.php of the jquery folder without a trailing slash
define(PLUG_PATH, 'CHANGEME');                               //Plugins folder path without the trailing slash
define(BOARDLIST, 'CHANGEME');       //the file that contains your boardlist, displayed at both header and footer [a/b/c/][d/e/f/] etc.
define(GLOBAL_NEWS, 'CHANGEME'); // Absolute html path to your global board news file, the contents of this file will be automatically
define(BLOTTER_PATH, 'CHANGEME'); // Absolute html path to your blotter file, this feature is experimental and still is not fully functional

////NEW TO SAGUARO 1.0
define(RES_DIR, 'res/'); //Directory 
define(UPDATE_THROTTLING, 0); //Leave this as 0 unless you recieve /a lot/ of traffic
define(CACHE_TTL, 1); // Thread caching 
define(EXPIRE_NEGLECTED, 1); // Bump old posts off the last page
define(FORTUNE_TRIP, 1); //Allows users to recieve a #fortune in the namefield
define(SALTFILE, 'salt'); //Name of the salt file, do not add a file extension for security



define(MAX_LINES_SHOWN, 20); // Maximum number of user lines shown before they are abbreviated
define(DICE_ROLL, 1); // Allow users to roll /dice in the name field
define(COUNTRY_FLAGS, 1); // Display poster's country flag with each post
define(SHOW_BLOTTER, 0);

// added to the top of each board, ex: ex: http://yoursite.com/resources/globalnews.txt
define(FORCED_ANON, 0); // Force anonymous on this board.
define(SPOILERS, 0); // Allow spoiler images/text on the board. This feature is still experimental and might not work fully.
define(MAX_LINES, 50); // Max # of lines allowed for a post
define(S_ANONAME, "Anonymous"); //Default name of all users who do not use a name



//Basic settings
define(TITLE, 'Saguaro beta!');                    //Name of this image board
define(S_HEADSUB, 'No artificial sweeteners!');             //subtitle underneath title
define(SHOWTITLETXT, 1);                              //Show TITLE at top (1: yes  0: no)
define(SHOWTITLEIMG, 0);                              //Show image at top (0: no, 1: single, 2: rotating)
define(TITLEIMG, '');                                   //Title image (point to php file if rotating)
define(LANGUAGE, 'en-us');                              //Language file to use from "lang" folder.
define(DATE_FORMAT, 'm/d/y');                           //Formatting for the date in each post, see http://php.net/manual/en/function.date.php for different options


/*From here down all these settings are optional.
Extra settings - No need to change these for a basic installation, but you may want these options*/

//Images
define(MAX_KB, 5048);                                 	//Maximum upload size in KB
define(MAX_W,  250);                                  	//OP images exceeding this width will be thumbnailed
define(MAX_H,  250);                                  	//OP images exceeding this height will be thumbnailed
define(MAXR_W,  125);                                 	//Image replies exceeding this width will be thumbnailed
define(MAXR_H,  125);                                 	//Image replies exceeding this height will be thumbnailed
define(MIN_W, 30);                                    		//minimum image dimensions - width
define(MIN_H, 30);                                    		//minimum image dimensions - height

//Pages
define(PAGE_DEF, 10);                                 	//Threads per page
define(PAGE_MAX, 10);							//Maximum number of pages, posts that are pushed past the last page are deleted.

//Posting & thread
define(RENZOKU, 10);                                	//Seconds between posts (floodcheck)
define(RENZOKU2, 13);                               	//Seconds between image posts (floodcheck)
define(S_POSTLENGTH, 3000);                        //Maximum character length of posts
define(MAX_RES, 500);                            	     //Maximum thread bumps
define(MAX_IMGRES, 300);
define(USE_THUMB, 1);                                   //Use thumbnails (1: yes  0: no)
define(PROXY_CHECK, 0);                             	//Enable proxy check (1: yes  0: no)
define(DISP_ID, 0);                                     	//Display user IDs (1: yes  0: no)
define(BR_CHECK, 20);                                     //Max lines per post (0 = no limit)
define(BOTCHECK, 0);                                  	//Use CAPTCHAs
define(S_OMITT_NUM, 5);                               //number of posts to display in each thread on the index
define(NOPICBOX, 0);                                  //whether or not to have the [No Picture] checkbox (1: yes  0: no)
define(USE_BBCODE, 1);                                //Use BBcode
define(S_SAGE, 'sage');                                  //What to change sage to
//Is this even referenced?! define(MANTHUMBS, '1');                                 //Display thumbnails in manager panel- you may want it off if you have too many images (1: yes  0: no)

//RePod's JS suite
define(USE_JS_SETTINGS, 1);                           //Include the JS suite's settings - enables user side settings
define(USE_IMG_HOVER, 1);                             //Use image expansion on hover
define(USE_IMG_TOOLBAR, 1);                         //Use the image search toolbars
define(USE_IMG_EXP, 1);                         	      //Use image expansion
define(USE_UTIL_QUOTE, 1);                           //Use utility quotes
define(USE_INF_SCROLL, 0);                           //Use infinite scroll
define(USE_FORCE_WRAP, 1);                      //Use forced post wrapping
define(USE_UPDATER, 1);                             //Use thread updater
define(USE_THREAD_STATS, 1);                   //Use thread stats
define(USE_EXTRAS, 1);                               //Automatically include all .js files in JS_PATH/extra/

//CSS stuff.
//These are required, but you can change them.
//TO-DO: Make scalable. - RePod
define(CSS1, 'saguaba.css'); 			//location of the first stylesheet.
define(CSS2, 'sagurichan.css'); 			//location of the second stylesheet.
define(CSS3, 'tomorrow.css'); 				//location of the third stylesheet.
define(CSS4, 'burichan.css');			 //location of the fourth stylesheet.


//Advanced Settings
define(IMG_DIR, 'src/');                                    //Image directory (needs to be 777)
define(THUMB_DIR,'thumb/');                                 //Thumbnail directory (needs to be 777)
define(HOME,  '/');                                         //Site home directory (up one level by default
define(LOG_MAX,  1500);                                   //Maxium number of entries
define(PHP_SELF, 'imgboard.php');                           //Name of main script file
define(PHP_SELF2, 'index.html');                            //Name of main htm file
define(PHP_EXT, '.html');                                   //Extension used for board pages after first



//Even more settings - there can never be enough

define(DUPE_CHECK, 1);                                //whether or not to check for duplicate images
define(S_DESCR, 'An imageboard powered by saguaro');    //meta description for this board
define(EXTRA_SHIT, '');                                 //Any extra javascripts you want to include inside the <head>


//Advertisements
define(USE_ADS1, 0);                            //Use advertisements (top) (1: yes  0: no)
define(ADS1, '<center>ads ads ads</center>');   //advertisement code (top)
define(USE_ADS2, 0);                            //Use advertisements (below post form) (1: yes  0: no)
define(ADS2, '<center>ads ads ads</center>');   //advertisement code (below post form)
define(USE_ADS3, 0);                            //Use advertisements (bottom) (1: yes  0: no)
define(ADS3, '<center>ads ads ads</center>');   //advertisement code (bottom)        

//BEWARE: Debug mode can display sensitive data that could be exploited. Use with caution
define(DEBUG_MODE, 0); //0: off, 1: on. Enabling this will display any SQL errors as well as making redirects between posting/log updates slower.


?>
