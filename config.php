<?php
/*
    Saguaro assumes you are running it from your public html root folder.
    If this is not the case, you will need to change your file pathing in the URL pathing section.

    ==============================================================================================
    MySQL information.
    The database and tables are created automatically using these values.
    Scroll below to the MySQL Advanced section for additional options.
*/
define('BOARD_DIR', basename(__DIR__));   //Local board directory name.
define('SQLUSER', 'username');
define('SQLPASS', 'password');
define('SQLHOST', 'localhost');

define('SQLDB', 'saguaro');   //Database used by image board.
define('SQLTABLES', "tables"); //Post table abstraction layer. 
define('SQLBANLOG', 'bans');  //Table for ban information.
define('SQLMODSLOG', 'mods'); //Table for mod information (authentication).
define('SQLDELLOG', 'dellog');  //Table for deleted information.
define('SQLBANNOTES', 'ipnotes'); //Table containing IP notes for warned/banned users
define('SQLREPORTS', 'reports');
define('SQLPROFILING', 'proclist');
define('SQLPROFILING2', 'prof_times');
define('SQLBLACKLIST', 'blacklist');
define('SQLPOSTS', "posts_" . BOARD_DIR); //Table to hold board posts.

define('SHOWTITLETXT', true); //Show title. 
define('SHOWTITLEIMG', true); //Show banner img.
define('LANGUAGE', "en-us");
define('TITLE', "Saguaro image board"); //Board name, displayed in header
define('S_HEADSUB', "Free range, grass fed imageboard!"); //Board subtitle, under header
define('S_DESCR', "I have not edited my config!"); //Meta description
define('S_ANONAME', "Anonymous"); //Default username.

//Other required.
define('PANEL_PASS', 'CHANGEME');  //Staff action key  (CHANGE THIS YO)
define('SITE_ROOT', 'example.com');//Site domain + root folder containing saguaro. No "http://" or "https://" etc. EX: example.com or sub.example.com or example.com/saguaro 
define('SITE_SUFFIX', preg_replace('/^.*\.(\w+)$/', '\1', SITE_ROOT));//Domain TLD. By default, this is obtained automatically with regex using SITE_ROOT.
define('NSFW',false); //NSFW board?
define('FILE_BOARD', false); //File upload board? Note: only accepts jpg,gif,png,webm as of 6/14/16

//Everything below this is technically optional.

//Catalog settings
define('STATIC_CATALOG', false); //Disable javascript catalog, build server-side. 
/* 
    Static catalog is useful for boards without the API enabled. May increase server load.
    If you use the static catalog, you can use the setting below to control how many seconds must
    pass between posts before the catalog page is rebuilt again
*/
define('CATALOG_THROTTLE', 5); //Amount of seconds between catalog rebuilds.


/* 
    Archive settings
    ARCHIVE_AGE is how long to keep posts archived for (in seconds)
    Default (604800 seconds) is 7 days.
    3600 seconds in a hour, 86400 seconds in a day.
    http://www.unitconversion.org/unit_converter/time.html
*/
define('ENABLE_ARCHIVE', true); //Enable archive, found at imgboard.php?mode=arc
define('ARCHIVE_AGE', 604800); 

//Post limits
define('BOTCHECK',false); //Enable captcha
define('DUPE_CHECK', true); //Check for duplicate images
define('ALLOW_SUBJECT_REPLY',false); //Allow replies with subject field
define('REQUIRE_SUBJECT',false); //Require subject field to be filled out for OPs
define('MAX_RES', 300); //Max # text replies to thread
define('MAX_IMGRES', 150); //Max # image replies to thread
define('EVENT_STICKY_RES', 750); //Cylical thread limit
define('RENZOKU', 10); //Seconds between text replies
define('RENZOKU2', 30); //Seconds between image replies
define('RENZOKU3', 120); //Seconds between thread creation


//Additional post features.
define('USE_BBCODE', true); //Enable markdown/BBcode. Read more here: https://github.com/spootTheLousy/saguaro/wiki/Text-Processors-(BBCode,-etc.)
define('DICE_ROLL', false); //Roll dice in the email field
define('FORTUNE_TRIP', false); //Enable a #fortune in the name field
define('FORCED_ANON',false); //Forced anonymous
define('DISP_ID', false); //Thread IDs
define('NOPICBOX',false); //Enable [No File] checkbox for OPs
define('SPOILERS', false); //Enable [Spoiler] checkbox. Disables NOPICBOX.

//Page options
define('PAGE_MAX', 15); //Max # of pages for a board
define('MAX_LINES', 50); //Maximum # of permitted post lines
define('MAX_LINES_SHOWN', 25); //Number of post lines shown before truncation. ["This post is too long, click here to view full text etc."]


define('STATIC_REBUILD', false); //Disable index rebuilding. No reason to enable this....yet.....
//define('EXPIRE_NEGLECTED', false); //Delete old posts.
define('BOARDLIST', 'CHANGEME');           //the text file that contains your boardlist, displayed at both header and footer [a/b/c/][d/e/f/] etc.
define('GLOBAL_NEWS', 'CHANGEME'); //Absolute html path to your global board news file. Appears below post form, above index body
define('SALTFILE', 'salt');        //Name of the salt file, do not add a file extension for security

define('USE_THUMB', true);    //Use thumbnails.
define('MAX_KB', 2048); //Maximum upload size in KB
define('S_POSTLENGTH', 3000); //Maximum character comment count for every 
define('PAGE_DEF', 15); //Threads per page.

//WebM
define('MAX_DURATION', 0);   //The maximum duration allowed in seconds. Set to 0 for unlimited length (still limited by filesize)

define('EXTRA_SHIT', ''); //Any extra html you want to include inside the <head>


//Default stylesheets
$cssArray = array(
	"Saguaba"	 => "/stylesheets/saguaba.css",
	"Sagurichan" => "/stylesheets/sagurichan.css",
	"Tomorrow"	 => "/stylesheets/tomorrow.css"
);

/*
    Advertisements. Add img tags, point to a script, hardlink to images, etc.
*/
define('ENABLE_ADS', false);
define('ADS_ABOVEFORM', '<center>ads ads ads</center>'); //Above post form
define('ADS_BELOWFORM', '<center>ads ads ads</center>'); //Immediately below post form
define('ADS_AFTERPOSTS', '<center>ads ads ads</center>'); //After last thread/post on a page.

/*
    Security settings.
    The defaults here are recommended.
*/

define('SECURE_LOGIN', false); //Enable CAPTCHA on staff login page.
define('JANITOR_CAPCODES',false); //Let janitors post with capcodes.
/*
    API Settings
*/
define('API_ENABLED', true);

/*
    Advanced settings.

    Everything past here should "just work" based on settings defined above, only needing fine tuning for enthusiasts.
    Specifically, anything user-unfriendly that a typical single (non-scaled multi-) board installation wouldn't need to worry about.
*/

//If board dir isn't defined, the config is being called by something that doesn't need BOARD_DIR or its children. 
//We'll just set it to null.

//URL pathing.
define('PHP_EXT', '.html');           //Extension used for board pages after first
define('PHP_SELF', 'imgboard.php');   //Name of main script file
define('PHP_SELF2', 'index' . PHP_EXT); //Name of main htm file
define('PHP_ASELF', 'admin.php');    // Name of Admin file
define('PHP_ASELF_ABS', '//'.SITE_ROOT.'/'.PHP_ASELF); //Path to admin file
define('SITE_ROOT_BD', SITE_ROOT.'/'. BOARD_DIR);
define('PHP_SELF_ABS', '//'.SITE_ROOT_BD.'/'.PHP_SELF);   // Absolute path from the site to the imgboard.php, ex: http://yoursite.com/boardDir/imgboard.php
define('PHP_SELF2_ABS', '//'.SITE_ROOT_BD.'/'.PHP_SELF2); // Absolute path from the site to the INDEX.html, ex: http://yoursite.com/boardDir/index.html
define('DATA_SERVER', '//'.SITE_ROOT.'/');                //Your site's root html path, WITH a trailing slash, ex: http://yoursite.com/
define('CSS_PATH', "//" . SITE_ROOT . '/css/');            //Absolute HTML!! path to the css folder with the trailing slash
define('PUBLIC_IMG_DIR', "//". SITE_ROOT . "/" . BOARD_DIR . "/"); //Public image directory HTML path
define('PUBLIC_THUMB_DIR', "//". SITE_ROOT . "/" . BOARD_DIR . "/"); //Public thumbnail directory HTML path
define('HOME', '../'); //Site home directory (up one level by default)

//Working directories.
define('CORE_DIR', '_core/');          //Local path to the "_core" directory, which contains the main assets of Saguaro.
define('CORE_DIR_PUBLIC', '//'.SITE_ROOT . "/" .CORE_DIR); //Public URL path to _core folder.
define('RES_DIR', 'res/');             //Stores cached threads.
define('IMG_DIR', 'src/');             //Stores images.
define('THUMB_DIR', 'thumb/');          //Stores thumbnails.
define('API_DIR', '/');                 //Where to put board's threads.json, catalog.json, [each index page #].json
define('API_DIR_RES', RES_DIR); //Where to store .json for each individual thread
define('PLUG_PATH', 'plugins/');       //Plugins folder.
define('PLUG_PATH_PUBLIC', '//'.SITE_ROOT .'/'.PLUG_PATH . "/"); //Public URL path to plugins folder.
define('JS_PATH', PLUG_PATH_PUBLIC.'js'); //jQuery folder. (usually in the plugins folder)



//Debug mode can display sensitive data that could be exploited, which is a huge security concern.
//This should be left off except when trying to find problems.
define('DEBUG_MODE', false);
define('PROFILING', false);
define('CLEAR_ON_SUCCESS', false); //If no issue with post shows up, delete the logged info.


//Image uploading.
define('MAX_W', 250);  //OP images exceeding this width will be thumbnailed
define('MAX_H', 250);  //OP images exceeding this height will be thumbnailed
define('MAXR_W', 125); //Image replies exceeding this width will be thumbnailed
define('MAXR_H', 125); //Image replies exceeding this height will be thumbnailed
define('MIN_W', 30);   //minimum image dimensions - width
define('MIN_H', 30);   //minimum image dimensions - height.

/* Planned for saguaro 3.0.5 Enable at your own peril.
define('S_OMITT_NUM', 5);
define('SHOW_PERMASAGE',false);
define('SHOW_ADMIN_LOGS',false);
define('NOKO_DEFAULT', true);
define('THREADS_PER_USER', 1); //How many threads a user can have at one.
*/

if (DEBUG_MODE == true) {
    ini_set('display_errors',1);
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}

?>