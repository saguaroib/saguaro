<?php


$configArray = [
    'LANGUAGE' => 'en-us',                      //Language to use. See "_core/lang/" folder for available languages.
    'TITLE' => 'Saguaro Imageboard!',           //Name of the board.
    'S_HEADSUB' => 'No artificial sweeteners!', //Board subtitle.
    'S_DESCR' => 'An imageboard powered by saguaro', //meta description for this board
    //Basic settings
    'NSFW' => false,    //Whether or not this is a NSFW(Red/Saguaba or blue/Sagurichan) board.
    'SHOWTITLETXT' => true,   //Show TITLE at top. False: hide title'' => true: display title (Setting this to 2 will show "/{your BOARD_DIR value}/ - {Your TITLE value}"
    'SHOWTITLEIMG' => false,  //Show image at top
    'TITLEIMG' => '',         //Title image (point to an img rotating script if you want rotating banners)
    'DATE_FORMAT' => 'm/d/y', //Formatting for the date in each post => see http://php.net/manual/en/function.date.php for different options
    'GIF_ONLY' => false, //GIF upload only imageboard.
    //Pages
    'PAGE_DEF' => 10, //Threads per page.
    'PAGE_MAX' => 10, //Maximum number of pages => posts that are pushed past the last page are deleted.
    'LOG_MAX' =>  1500, //Maximum number of posts to store in the table.
    'UPDATE_THROTTLING' => false, //Leave this as 0 unless you recieve /a lot/ of traffic
    'SHOW_BLOTTER' => false,      //Experimental. Added to the top of each board => ex: ex: http://yoursite.com/resources/globalnews.txt
    //Administrative
    'JANITOR_CAPCODES' => false, //Allow janitors to post with a capcode
    'REPORT_FLOOD' => 5, //How many reports a user can file at once.
    // Post & Thread
    'USE_BBCODE' => false,  //Use BBcode
    'DICE_ROLL' => false,   //Allow users to roll /dice in the name field
    'FORTUNE_TRIP' => false, //Allows users to recieve a #fortune in the namefield

    'FORCED_ANON' => false, //Force anonymous on this board.
    'DISP_ID' => false,     //Display user IDs.
    'MAX_LINES' => 50,      //Max # of line breaks allowed for a post
    'MAX_LINES_SHOWN' => 20,      //Maximum number of user lines shown in the index before they are abbreviated
    'S_POSTLENGTH' => 3000, //Maximum character length of posts
    'NOPICBOX' => false,    //Whether or not to have the [No Picture] checkbox.

    'USE_THUMB' => true,    //Use thumbnails.
    'PROXY_CHECK' => true,  //Enable proxy check.

    'COOLDOWN_POST' => 10,    //Cooldown between new posts without files => in seconds.
    'COOLDOWN_FILE' => 15,    //Cooldown between new posts with files => in seconds.
    'COOLDOWN_THREAD' => 13,  //Cooldown between new threads => in seconds.
    'MAX_RES' => 500,       //Maximum thread bumps from posts.
    'STRICT_FILE_COUNT' => false, //If true => accounts for multi-file posts otherwise each post is counted as one regardless of amount of files.
    'MAX_IMGRES' => 300,    //Maximum thread bumps from images
    'EVENT_STICKY_RES' => 1500, //The number of replies allowed to an event sticky before in-thread pruning begins. These stickies self delete the oldest posts once the number of replies exceeds this number.
    'S_OMITT_NUM' => 5,     //number of posts to display in each thread on the index.
    //Captcha
    'BOTCHECK' => false,    //Use CAPTCHAs
    'RECAPTCHA' => false, //Use reCaptcha instead of the default captcha. Requires the SITEKEY and SECRET to be set below.
    'RECAPTCHA_SITEKEY' => "",//reCaptcha public key.
    'COUNTRY_FLAGS' => false,     //Display poster's country flag with each post
    'S_ANONAME' => 'Anonymous'   //Default name of all users who do not use a name

];