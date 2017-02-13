<?php
/*
    General, board-specific settings.
*/

define('LANGUAGE', 'en-us');                      //Language to use. See "_core/lang/" folder for available languages.
define('TITLE', 'Saguaro Imageboard!');           //Name of the board.
define('S_HEADSUB', 'No artificial sweeteners!'); //Board subtitle.
define('S_DESCR', 'An imageboard powered by saguaro'); //meta description for this board


/*
    MySQL information.
    The database and tables are created automatically using these values.
    Scroll below to the MySQL Advanced section for additional options.
*/
define('SQLUSER', 'username');
define('SQLPASS', 'password');
define('SQLHOST', 'localhost');

define('SQLDB', 'saguaro');   //Database used by image board.
define('PREFIX', 'imgboard'); //Prefix to automatically use for the database tables.


/*
    Something descriptive.
*/

define('PANEL_PASS', 'CHANGEME');  //Staff action key  (CHANGE THIS YO)
define('SITE_ROOT', 'example.com');//Site domain.
define('SALTFILE', 'salt');        //Name of the salt file, do not add a file extension for security

//Basic settings
define('NSFW', false);    //Whether or not this is a NSFW(Red/Saguaba or blue/Sagurichan) board.
define('SHOWTITLETXT', true);   //Show TITLE at top. False: hide title'', true: display title (Setting this to 2 will show "/{your BOARD_DIR value}/ - {Your TITLE value}"
define('SHOWTITLEIMG', false);  //Show image at top
define('TITLEIMG', '');         //Title image (point to an img rotating script if you want rotating banners)
define('DATE_FORMAT', 'm/d/y'); //Formatting for the date in each post, see http://php.net/manual/en/function.date.php for different options


/*
    Specialized board settings - a board with specific purpose
*/

define('GIF_ONLY', false); //GIF upload only imageboard.


/*
    Posting'', threads, and images.
*/

//Pages
define('PAGE_DEF', 10); //Threads per page.
define('PAGE_MAX', 10); //Maximum number of pages, posts that are pushed past the last page are deleted.
define('LOG_MAX',  1500); //Maximum number of posts to store in the table.
define('UPDATE_THROTTLING', false); //Leave this as 0 unless you recieve /a lot/ of traffic
define('ENABLE_BLOTTER', false); //Show blotter under postform. Edit blotter contents from admin panel.
define('STATIC_CATALOG', false); //Enables catalog that doesn't need javascript to load. Catalog page may load slower if you have a lot of threads.

//Administrative
define('JANITOR_CAPCODES', false); //Allow janitors to post with a capcode
define('REPORT_FLOOD', 5); //How many reports a user can file at once.

// Post & Thread
define('USE_BBCODE', false);  //Use BBcode
define('DICE_ROLL', false);   //Allow users to roll /dice in the name field
define('FORTUNE_TRIP', false); //Allows users to recieve a #fortune in the namefield

define('FORCED_ANON', false); //Force anonymous on this board.
define('DISP_ID', false);     //Display user IDs.
define('MAX_LINES', 50);      //Max # of line breaks allowed for a post
define('MAX_LINES_SHOWN', 20);      //Maximum number of user lines shown in the index before they are abbreviated
define('S_POSTLENGTH', 3000); //Maximum character length of posts
define('NOPICBOX', false);    //Whether or not to have the [No Picture] checkbox.

define('USE_THUMB', true);    //Use thumbnails.
define('PROXY_CHECK', true);  //Enable proxy check.

define('COOLDOWN_POST', 10);    //Cooldown between new posts without files, in seconds.
define('COOLDOWN_FILE', 15);    //Cooldown between new posts with files, in seconds.
define('COOLDOWN_THREAD', 13);  //Cooldown between new threads, in seconds.
define('MAX_RES', 500);       //Maximum thread bumps from posts.
define('STRICT_FILE_COUNT', false); //If true, accounts for multi-file posts otherwise each post is counted as one regardless of amount of files.
define('MAX_IMGRES', 300);    //Maximum thread bumps from images
define('EVENT_STICKY_RES', 1500); //The number of replies allowed to an event sticky before in-thread pruning begins. These stickies self delete the oldest posts once the number of replies exceeds this number.
define('S_OMITT_NUM', 5);     //number of posts to display in each thread on the index.

//Captcha
define('BOTCHECK', false);    //Use CAPTCHAs
define('RECAPTCHA', false); //Use reCaptcha instead of the default captcha. Requires the SITEKEY and SECRET to be set below.
define('RECAPTCHA_SITEKEY', "");//reCaptcha public key.
define('RECAPTCHA_SECRET', "");//reCaptcha secret key.

//Images
define('DUPE_CHECK', true); //whether or not to check for duplicate images
define('MAX_KB', 2048); //Maximum upload size in KB
define('MAX_FILE_COUNT', 1); //Maximum number of media attachments to allow per post

//WebM
define('ALLOW_WEBMS', false); //This feature currently has prequisites. Please visit https://github.com/spootTheLousy/saguaro/wiki/Supporting-WEBMs before enabling.
define('ALLOW_AUDIO', false); //If true, allows WebMs containing an audio stream.
define('MAX_DURATION', 60);   //The maximum duration allowed in seconds.

/*
	CSS settings
	To include additional CSS, drop them in your /css/stylesheets/ and add them to the array!
	Format is: "Display Name" => "path/to/cssfile.css"
*/
$cssArray = array(
	"Saguaba"	 => "/stylesheets/saguaba.css", 	//First array value is the default NSFW stylesheet
	"Sagurichan" => "/stylesheets/sagurichan.css", 	//Second array value is the default SFW stylesheet
	//Order doesnt matter for the rest of these
	"Tomorrow"	 => "/stylesheets/tomorrow.css"
);

define('EXTRA_SHIT', ''); //Any extra javascripts you want to include inside the <head>

/*
    Advertisements.
*/
define('ENABLE_ADS', false);                      //Use advertisements (top)
define('ADS_ABOVEFORM', '<center>ads ads ads</center>'); //advertisement code (top)
define('ADS_BELOWFORM', '<center>ads ads ads</center>'); //advertisement code (below post form)
define('ADS_AFTERPOSTS', '<center>ads ads ads</center>'); //advertisement code (bottom)


/*
    Security settings.

    The defaults here are recommended.
*/

define('SECURE_LOGIN', true); //Enable CAPTCHA on staff login page.


/*
    Advanced settings.

    Everything past here should "just work" based on settings defined above, only needing fine tuning for enthusiasts.
    Specifically, anything user-unfriendly that a typical single (non-scaled multi-) board installation wouldn't need to worry about.
*/

//Debug mode can display sensitive data that could be exploited, which is a huge security concern.
//This should be left off except when trying to find problems.
define('DEBUG_MODE', false);

/*
MySQL tables. Only change these if defaults are not desired.

By default', these tables are generated unique per-board.
To share tables (login, bans, posts, etc.) between boards, delete PREFIX. or see the wiki page:
https://github.com/spootTheLousy/saguaro/wiki/Board-SQL-Table-relationship
*/

define('SQLLOG', PREFIX);            //Table for posting information.
define('SQLBANLOG', PREFIX.'_ban');  //Table for ban information.
define('SQLMODSLOG', PREFIX.'_mod'); //Table for mod information (authentication).
define('SQLDELLOG', PREFIX.'_del');  //Table for deleted information.
define('SQLBANNOTES', PREFIX.'_ipnotes'); //Table containing IP notes for warned/banned users
define('SQLMEDIA', PREFIX.'_media'); //Table for media (or files in general) information.
define('SQLREPORTS', PREFIX.'_reports'); //Table for report information.
define('SQLRESOURCES', PREFIX.'_resources'); //Table for boardList, announcements and blotter.

//URL pathing.
define('SITE_SUFFIX', preg_replace('/^.*\.(\w+)$/', '\1', SITE_ROOT));//Domain TLD. By default, this is obtained automatically with regex using SITE_ROOT.
define('BOARD_DIR', basename(__DIR__)); //Folder name of board, EX: /ba/ would be ba. Defaults to the current folder's name.
define('PHP_EXT', '.html');           //Extension used for board pages after first
define('PHP_SELF', 'imgboard.php');   //Name of main script file
define('PHP_SELF2', 'index'.PHP_EXT); //Name of main htm file
define('PHP_ASELF', 'admin.php');    // Name of Admin file
define('PHP_ASELF_ABS', '//'.SITE_ROOT.'/'.BOARD_DIR.'/'.PHP_ASELF); //Path to admin file
define('SITE_ROOT_BD', '//' . SITE_ROOT.'/'.BOARD_DIR);
define('PHP_SELF_ABS', '//'.SITE_ROOT_BD.'/'.PHP_SELF);   // Absolute path from the site to the imgboard.php, ex: http://yoursite.com/boardDir/imgboard.php
define('PHP_SELF2_ABS', '//'.SITE_ROOT_BD.'/'.PHP_SELF2); // Absolute path from the site to the INDEX.html, ex: http://yoursite.com/boardDir/index.html
define('DATA_SERVER', '//'.SITE_ROOT.'/');                //Your site's root html path, WITH a trailing slash, ex: http://yoursite.com/
define('CSS_PATH', '//'.SITE_ROOT_BD.'/css/');            //absolute html path to the css folder with the trailing slash
define('HOME', '..'); //Site home directory (up one level by default)


//Working directories.
define('CORE_DIR', '_core/');          //Local path to the "_core" directory, which contains the main assets of Saguaro.
define('CORE_DIR_PUBLIC', '//'.SITE_ROOT_BD.'/'.CORE_DIR); //Public URL path to _core folder.
define('RES_DIR', 'res/');             //Stores cached threads.
define('IMG_DIR', 'src/');             //Stores images.
define('THUMB_DIR','thumb/');          //Stores thumbnails.
define('PLUG_PATH', 'js/');       //Plugins folder.
define('PLUG_PATH_PUBLIC', '//'.SITE_ROOT_BD.'/'.PLUG_PATH); //Public URL path to plugins folder.
define('JS_PATH', PLUG_PATH_PUBLIC); //jQuery folder. (usually in the plugins folder)
define('PUBLIC_IMAGE_DIR', '//'.SITE_ROOT_BD.'/'.IMG_DIR); //Web path to a board's image folder
define('PUBLIC_THUMB_DIR', '//'.SITE_ROOT_BD.'/'.THUMB_DIR);//Web path to a board's thumbnail folder

//Posting and Threads
define('CACHE_TTL', true);          //Thread caching
define('EXPIRE_NEGLECTED', true);   //Bump old posts off the last page
define('S_SAGE', 'sage');           //What to change sage to
define('COUNTRY_FLAGS', false);     //Display poster's country flag with each post
define('S_ANONAME', 'Anonymous');   //Default name of all users who do not use a name

//Image uploading.
define('MAX_W', 250);  //OP images exceeding this width will be thumbnailed
define('MAX_H', 250);  //OP images exceeding this height will be thumbnailed
define('MAXR_W', 125); //Image replies exceeding this width will be thumbnailed
define('MAXR_H', 125); //Image replies exceeding this height will be thumbnailed
define('MIN_W', 30);   //minimum image dimensions - width
define('MIN_H', 30);   //minimum image dimensions - height

$badstring = ["nimp.org"]; // Refused text. Currently unused by Regist.
$badfile = ["dummy", "dummy2"]; //Refused files (md5 hashes). Currently unused by Regist.

include(CORE_DIR . "/lang/language.php");

if (DEBUG_MODE == true) {
    ini_set('display_errors',1);
    error_reporting(E_ALL & ~E_NOTICE);
}

?>