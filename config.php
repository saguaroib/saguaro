<?php
/*
    General, board-specific settings.
*/

define(LANGUAGE, 'en-us');                      //Language to use. See "lang" folder for available languages.
define(TITLE, 'Saguaro beta!');                 //Name of the board.
define(S_HEADSUB, 'No artificial sweeteners!'); //Board subtitle.
define(S_DESCR, 'An imageboard powered by saguaro'); //meta description for this board



/*
    MySQL information.
    The database and tables are created automatically using these values.
*/
define(SQLUSER, 'user');
define(SQLPASS, 'password');
define(SQLHOST, 'localhost');

define(SQLDB, 'saguaro');   //Database used by image board.
define(PREFIX, 'imgboard'); //Prefix to automatically use for the database tables.



/*
    Something descriptive.
*/

define(PANEL_PASS, 'CHANGEME');  //Janitor password  (CHANGE THIS YO)
define(BOARD_DIR, 'saguaro');    //Folder name of board, EX: /ba/ would be ba
define(SITE_ROOT, 'MYSITE.COM'); //simplified site domain ONLY, EX: saguaro.org
define(SITE_SUFFIX, '');         //Domain suffix, ex: org, com, info, net. NO DOTS, ONLY LETTERS
define(BOARDLIST, 'CHANGEME');   //the file that contains your boardlist, displayed at both header and footer [a/b/c/][d/e/f/] etc.
define(GLOBAL_NEWS, 'CHANGEME'); //Absolute html path to your global board news file, the contents of this file will be automatically
define(SALTFILE, 'salt');        //Name of the salt file, do not add a file extension for security

//Directories.
define(CORE_DIR, '_core/');          //Local path to the "_core" directory, which contains the main assets of Saguaro.
define(RES_DIR, 'res/');             //Stores cached threads.
define(IMG_DIR, 'src/');             //Stores images.
define(THUMB_DIR,'thumb/');          //Stores thumbnails.
define(PLUG_PATH, 'plugins/');       //Plugins folder.
define(JS_PATH, PLUG_PATH.'jquery'); //jQuery folder. (usually in the plugins folder)

//Basic settings
define(SHOWTITLETXT, true);   //Show TITLE at top.
define(SHOWTITLEIMG, 0);      //Show image at top (0: no, 1: single, 2: rotating)
define(TITLEIMG, '');         //Title image (point to php file if rotating)
define(DATE_FORMAT, 'm/d/y'); //Formatting for the date in each post, see http://php.net/manual/en/function.date.php for different options



/*
    Posting, threads, and images.
*/

//Pages
define(PAGE_DEF, 10); //Threads per page.
define(PAGE_MAX, 10); //Maximum number of pages, posts that are pushed past the last page are deleted.
define(LOG_MAX,  1500); //Maximum number of posts to store in the table.
define(UPDATE_THROTTLING, false); //Leave this as 0 unless you recieve /a lot/ of traffic
define(MAX_LINES_SHOWN, 20);      //Maximum number of user lines shown before they are abbreviated
define(SHOW_BLOTTER, false);      //Added to the top of each board, ex: ex: http://yoursite.com/resources/globalnews.txt
define(BLOTTER_PATH, 'CHANGEME'); //Absolute html path to your blotter file, this feature is experimental and still is not fully functional.

// Post & Thread
define(FORCED_ANON, false); //Force anonymous on this board.
define(MAX_LINES, 50);      //Max # of lines allowed for a post
define(RENZOKU, 10);        //Seconds between posts (floodcheck)
define(RENZOKU2, 15);       //Seconds between image posts (floodcheck)
define(S_POSTLENGTH, 3000); //Maximum character length of posts
define(MAX_RES, 500);       //Maximum thread bumps from posts.
define(MAX_IMGRES, 300);    //Maximum thread bumps from images.
define(USE_THUMB, true);    //Use thumbnails.
define(PROXY_CHECK, true);  //Enable proxy check.
define(DISP_ID, false);     //Display user IDs.
define(BR_CHECK, 20);       //Max lines per post (0 = no limit)
define(BOTCHECK, false);    //Use CAPTCHAs
define(S_OMITT_NUM, 5);     //number of posts to display in each thread on the index.
define(NOPICBOX, false);    //Whether or not to have the [No Picture] checkbox.
define(USE_BBCODE, true);   //Use BBcode
define(DICE_ROLL, false);   //Allow users to roll /dice in the name field
define(SPOILERS, false);    //Allow spoiler images/text on the board. This feature is still experimental and might not work fully.
define(FORTUNE_TRIP, true); //Allows users to recieve a #fortune in the namefield
//Is this even referenced?! define(MANTHUMBS, '1');                                 //Display thumbnails in manager panel- you may want it off if you have too many images (1: yes  0: no)

//Images
define(DUPE_CHECK, true); //whether or not to check for duplicate images
define(MAX_KB, 5048); //Maximum upload size in KB

//RePod's JS suite
define(USE_JS_SETTINGS, 1); //Include the JS suite's settings - enables user side settings
define(USE_IMG_HOVER, 1);   //Use image expansion on hover
define(USE_IMG_TOOLBAR, 1); //Use the image search toolbars
define(USE_IMG_EXP, 1);     //Use image expansion
define(USE_UTIL_QUOTE, 1);  //Use utility quotes
define(USE_INF_SCROLL, 0);  //Use infinite scroll
define(USE_FORCE_WRAP, 1);  //Use forced post wrapping
define(USE_UPDATER, 1);     //Use thread updater
define(USE_THREAD_STATS, 1); //Use thread stats
define(USE_EXTRAS, 1);      //Automatically include all .js files in JS_PATH/extra/



/*
    CSS
*/
define(CSS1, 'saguaba.css');    //location of the first stylesheet.
define(CSS2, 'sagurichan.css'); //location of the second stylesheet.
define(CSS3, 'tomorrow.css');   //location of the third stylesheet.
define(CSS4, 'burichan.css');   //location of the fourth stylesheet.

define(EXTRA_SHIT, '');  //Any extra javascripts you want to include inside the <head>



/*
    Advertisements.
*/
define(USE_ADS1, 0);                            //Use advertisements (top) (1: yes  0: no)
define(ADS1, '<center>ads ads ads</center>');   //advertisement code (top)
define(USE_ADS2, 0);                            //Use advertisements (below post form) (1: yes  0: no)
define(ADS2, '<center>ads ads ads</center>');   //advertisement code (below post form)
define(USE_ADS3, 0);                            //Use advertisements (bottom) (1: yes  0: no)
define(ADS3, '<center>ads ads ads</center>');   //advertisement code (bottom)



/*
    Advanced settings.

    Everything past here should "just work" based on settings defined above, only needing fine tuning for enthusiasts.
    Specifically, anything user-unfriendly that a typical single (non-scaled multi-) board installation wouldn't need to worry about.
*/

//BEWARE: Debug mode can display sensitive data that could be exploited. Use with caution
define(DEBUG_MODE, 0); //0: off, 1: on. Enabling this will display any SQL errors as well as making redirects between posting/log updates slower.s

//MySQL tables. Only change these if defaults are not desired.
define(SQLLOG, PREFIX);            //Table for posting information.
define(SQLBANLOG, PREFIX.'_ban');  //Table for ban information.
define(SQLMODSLOG, PREFIX.'_mod'); //Table for mod information (authentication).
define(SQLDELLOG, PREFIX.'_del');  //Table for deleted information.

//URL pathing.
define(PHP_EXT, '.html');           //Extension used for board pages after first
define(PHP_SELF, 'imgboard.php');   //Name of main script file
define(PHP_SELF2, 'index'.PHP_EXT); //Name of main htm file
define(SITE_ROOT_BD, SITE_ROOT.'/'.BOARD_DIR);
define(PHP_SELF_ABS, '//'.SITE_ROOT_BD.'/'.PHP_SELF);   // Absolute path from the site to the imgboard.php, ex: http://yoursite.com/boardDir/imgboard.php
define(PHP_SELF2_ABS, '//'.SITE_ROOT_BD.'/'.PHP_SELF2); // Absolute path from the site to the INDEX.html, ex: http://yoursite.com/boardDir/index.html
define(DATA_SERVER, '//'.SITE_ROOT.'/');                //Your site's root html path, WITH a trailing slash, ex: http://yoursite.com/
define(CSS_PATH, '//'.SITE_ROOT_BD.'/css/');            //absolute html path to the css folder with the trailing slash
define(HOME,  '..'); //Site home directory (up one level by default)

//Posting and Threads
define(CACHE_TTL, true);          //Thread caching
define(EXPIRE_NEGLECTED, true);   //Bump old posts off the last page
define(S_SAGE, 'sage');           //What to change sage to
define(COUNTRY_FLAGS, false);     //Display poster's country flag with each post
define(S_ANONAME, "Anonymous");   //Default name of all users who do not use a name

//Image uploading.
define(MAX_W, 250);  //OP images exceeding this width will be thumbnailed
define(MAX_H, 250);  //OP images exceeding this height will be thumbnailed
define(MAXR_W, 125); //Image replies exceeding this width will be thumbnailed
define(MAXR_H, 125); //Image replies exceeding this height will be thumbnailed
define(MIN_W, 30);   //minimum image dimensions - width
define(MIN_H, 30);   //minimum image dimensions - height

?>
