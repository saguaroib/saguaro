<?php

global $path, $host, $pwdc, $textonly, $my_log, $mysql, $dest;

if (array_key_exists('upfile', $_FILES)) {
    $upfile_name = $_FILES["upfile"]["name"];
    $upfile = $_FILES["upfile"]["tmp_name"];
} else {
    $upfile_name = $upfile = '';
}
$files = array();

/*foreach($_FILES as $array) {
    array_push($files, $array);
}
echo "<pre>";
var_dump($files);
exit();*/
require_once('inc/uploadcheck.php'); //Check prereq conditions for post processing
$check = new UploadCheck;
$check->run();

$admin = valid('moderator');

if (!$upfile && !$resto) { // allow textonly threads for moderators!
    $textonly = (valid('textonly')) ? 1 : 0;
}

$time = time();
$tim = $time . substr(microtime(), 2, 3);
$locked_time = $time; //Getting initialization times.

if (!isset($my_log->cache) || empty($my_log->cache))
    $my_log->update_cache();

$log = $my_log->cache;

$mysql->query("SET session query_cache_type=0");
$resto = (int) $resto;
$board = BOARD_DIR;
if ($resto)
    $isThread = (int) $mysql->result("SELECT resto FROM " . SQLLOG . " WHERE no='$resto' AND board='$board'");
if ($isThread > 0) {
    error(S_NOTHREADERR, $dest);
}

/*if (!isset($log[$resto]))
error(S_NOTHREADERR, $dest);*/

if ($resto) { //Check if thread is locked.
    if ($log[$resto]['locked'] == 1 && !$admin)
        error(S_THREADLOCKED, $upfile);
    if ($log[$resto]['locked'] == 2)
        error(S_THREADARCHIVED, $upfile);
}

/*
Load in the classes we'll need
*/
/*
require_once("inc/filters.php");
$filter = new Filter;*/
require_once("inc/sanitize.php");
$sanitize = new Sanitize;

require("inc/upload_file.php"); //Process uploaded image.

// Form content check
if (!$name || preg_match("/^[ |&#12288;|]*$/", $name))
    $name = "";
if (!$com || preg_match("/^[ |&#12288;|\t]*$/", $com))
    $com = "";
if (!$sub || preg_match("/^[ |&#12288;|]*$/", $sub))
    $sub = "";

if (NO_TEXTONLY && !$admin) {
    if (!$resto && !$has_image)
        error(S_NOPIC, $dest);
} else {
    if (!$resto && !$textonly && !$has_image)
        error(S_NOPIC, $dest);
}
if (strlen(trim($com)) < 1 && !$has_image) //Still require text for embedded videos
    error(S_NOTEXT, $dest);

if (!$admin && strlen($com) > 2000)
    error(S_TOOLONG, $dest);
if (strlen($name) > 100)
    error(S_TOOLONG, $dest);
if (strlen($email) > 100)
    error(S_TOOLONG, $dest);
if (strlen($sub) > 100)
    error(S_TOOLONG, $dest);
if (strlen($resto) > 10)
    error(S_UNUSUAL, $dest);
if (strlen($url) > 10)
    error(S_UNUSUAL, $dest);

logme("starting autoban checks");
//$filter->post($com, $sub, $name, $fsize, $resto, $W, $H, $dest, $upfile_name, $email);

$xff = getenv("HTTP_X_FORWARDED_FOR");
//$filter->host($dest);

// No, path, time, and url format
if ($pwd == "") {
    if ($pwdc == "") {
        $pwd = rand();
        $pwd = substr($pwd, 0, 8);
    } else {
        $pwd = $pwdc;
    }
}

$c_pass = $pwd;
$pass   = ($pwd) ? substr(md5($pwd), 2, 8) : "*";
$youbi  = array(S_SUN,S_MON,S_TUE,S_WED,S_THU,S_FRI,S_SAT);

$yd  = $youbi[date("w", $time)];
$now = (SHOW_SECONDS) ? date("m/d/y", $time) . " (" . (string) $yd . ") " . date("H:i:s", $time) : date("m/d/y", $time) . " (" . (string) $yd . ") " . date("H:i", $time);

if (COUNTRY_FLAGS) {
    require_once('inc/geoiploc.php');
    $flag    = strtolower(getCountryFromIP($_SERVER['REMOTE_ADDR'], "abbr"));
    $country = getCountryFromIP($_SERVER['REMOTE_ADDR'], "name");
    $now .= ' <span class="flag f-' . $flag . '" title="' . $country . '" ></span>';
}

if (DISP_ID)
    $now .= ($email === "sage" && DISP_ID) ? " ID: Heaven" : " (ID:" . substr( crypt(md5($_SERVER["REMOTE_ADDR"] . date("Ymd", $time)), 'id') , 3, 12) . ")";

$c_name  = $name;
$c_email = $email;

//Text plastic surgery (rorororor) NOTE: Also applies greentext here
$email = $sanitize->CleanStr($email);
$email = preg_replace("/[\r\n]/", "", $email);
$sub   = $sanitize->CleanStr($sub);
$sub   = preg_replace("/[\r\n]/", "", $sub);
$url   = $sanitize->CleanStr($url);
$url   = preg_replace("/[\r\n]/", "", $url);
$resto = $sanitize->CleanStr($resto);
$resto = preg_replace("/[\r\n]/", "", $resto);
$com   = $sanitize->CleanStr($com, $admin, 1);

if ($admin)
    $com = htmlspecialchars($com); //WEW
//$com = preg_replace("!(^|>)(&gt;[^<]*)!", "\\1<font class=\"quote\">\\2</font>", $com);
if ($admin)
    $com = htmlspecialchars_decode($com); //LADS

$sub = (SPOILERS && $_POST['spoiler']) ? "SPOILER<>$sub" : $sub;
// Standardize new character lines
$com = str_replace("\r\n", "\n", $com);
$com = str_replace("\r", "\n", $com);
//$com = preg_replace("/\A([0-9A-Za-z]{10})+\Z/", "!s8AAL8z!", $com);
// Continuous lines
$com = preg_replace("/\n((&#12288;| )*\n){3,}/", "\n", $com);

if (!$admin && substr_count($com, "\n") > MAX_LINES)
    error(S_TOOMANYLINES, $dest);

$com = nl2br($com); //br is substituted before newline char

$com = str_replace("\n", "", $com); //\n is erased
/*
if (ROBOT9000) {
$r9k = $filter->r9k($com, $md5, valid('floodbypass'));
if ($r9k != "ok")
error($r9k, $dest);
}*/

$name  = preg_replace("/[\r\n]/", "", $name);
$names = iconv("UTF-8", "CP932//IGNORE", $name); // convert to Windows Japanese #&#65355;&#65345;&#65357;&#65353;

require("inc/addons.php"); //Fortune, tripcodes, dice rolling.

if (strlen(trim($name)) < 1)
    $name = S_ANONAME;
if (strlen(trim($com)) < 1)
    $com = S_ANOTEXT;
if (strlen(trim($sub)) < 1)
    $sub = S_ANOTITLE;

$nameparts = explode('</span> <span class="postertrip">!', $name);
/*
$filter->blacklist(array(
'name' => $nameparts[0],
'trip' => $trip,
'nametrip' => "{$nameparts[0]} #{$trip}",
'md5' => $md5,
'email' => $email,
'sub' => $sub,
'com' => $com,
'pwd' => $pass,
'xff' => $xff,
'filename' => $insfile
), $dest);

$filter->tripcode($name, $trip, $dest);*/

if (USE_BBCODE) {
    require_once(CORE_DIR . '/general/text_process/bbcode.php');
    $bbcode = new BBCode;
    $com    = $bbcode->format($com);
}

if (WORD_FILT) {
    //$com = $filter->simpleFilter($com, "com");
    // if ($sub)
    //$sub =  $filter->simpleFilter($sub, "sub");
    
    $namearr = explode('</span> <span class="postertrip">', $name);
    if (strstr($name, '</span> <span class="postertrip">')) {
        $nametrip = '</span> <span class="postertrip">' . $namearr[1];
    } else {
        $nametrip = "";
    }
    //if ($namearr[0] != S_ANONAME)
    //$name =  $filter->simpleFilter($namearr[0], "name") . $nametrip;
}

if (FORCED_ANON) {
    $name = "</span>$now<span>";
    $sub  = '';
    $now  = '';
}

if (FILE_BOARD && !$resto) {
    switch ($_POST['tagSelect']) {
        case 1: //Hentai
            $now .= " Hentai";
            break;
        case 2: //Porn
            $now .= " Porn";
            break;
        case 3: //Japanese
            $now .= " Japanese";
            break;
        case 4: //Anime
            $now .= " Anime";
            break;
        case 5: //Game
            $now .= " Game";
            break;
        case 6: //Loop
            $now .= " Loop";
            break;
        case 7: //Music
            $now .= " Music";
            break;
        case 8: //Other 
            $now .= " Other";
            break;
        default: //Notag
            error(S_BADTAG, $dest);
    }
}

$com = $sanitize->wordwrap2($com, 100, "<br />");

$is_sage = (stripos($email, "sage") !== FALSE || stripos($email, "nokosage") !== FALSE) ? true : false;
//Finished post processing.

//Flood checks.
logme("Before flood check");
if (!$admin) {
    require_once("inc/flood.php");
}

//Create thumbnail
if ($has_image) {
    rename($dest, $path . $tim . $ext);
    if (USE_THUMB) {
        require_once("inc/process/thumb.php");
        $tn_name = thumb($path, $tim, $ext, $resto);
        if (!$tn_name && $ext != ".pdf") {
            error(S_UNUSUAL);
        }
    }
}
logme("Thumbnail created");

// noko (stay) actions
if ($email == 'noko') {
    $email = '';
    $noko  = 1;
}

$noko = true; //Force it for now..

//Special actions for moderators
$sticky   = false;
$autosage = false; //$filter->autosage($com, $sub, $name, $fsize, $resto, $W, $H, $dest, $insertid);
$stickied = ($_POST['isSticky'] && $admin) ? 1 : ($_POST['eventSticky'] && $admin) ? 2 : null;
$locked   = ($_POST['isLocked'] && $admin) ? 1 : null;

$lastno   = (int) $mysql->result("SELECT MAX(no) FROM " . SQLLOG . " WHERE board='$board'");
$insertid = ++$lastno; //cs_graduate.jpg
logme("Got last insert id ($lastno)");

logme("Attempting insert for ($insertid)");

//Main insert
$updateCache = ['no', 'now','name','email','sub','com','host','pwd','ext','w','h','tn_w','tn_h','tim','time','md5','fsize','fname','embed','sticky','permasage','locked', 'resto', 'board'];
$updateCacheWith = [$insertid, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $W, $H, $TN_W, $TN_H, $tim,$time, $md5, $fsize, $upfile_name, $embed, $sticky, $permasage, $locked, $resto, BOARD_DIR];

foreach ($updateCache as $key) {
    $queryKeys .= "{$key},";
}

foreach ($updateCacheWith as $value) {
    if (is_numeric($value)) {
        $queryValues .= "'" . (int) $value . "',";
    } else {
        $queryValues .= "'" . $mysql->escape_string($value) . "',";
    }
}

//Remove trailing comma
$queryKeys   = rtrim($queryKeys, ",");
$queryValues = rtrim($queryValues, ",");

$query = "INSERT INTO " . SQLLOG . " ({$queryKeys}) VALUES ({$queryValues})";

if (!$result = $mysql->query($query)) {
    error(S_REGFAILED);
} //End main insert.

foreach ($updateCache as $key) { //If post inserted into db correctly, we're safe to manually insert new post data into the cache.
    foreach ($updateCacheWith as $value) {
        $my_log->cache[$insertid][$key] = $value;
    }
}

//Cookies
$cookie_domain = '.' . SITE_ROOT;
setrawcookie("saguaro_name", rawurlencode($c_name), time() + ($c_name ? (7 * 24 * 3600) : -3600), '/', $cookie_domain);
if (($c_email != "sage"))
    setcookie("saguaro_email", $c_email, time() + ($c_email ? (7 * 24 * 3600) : -3600), '/', $cookie_domain); // 1 week cookie expiration
setcookie("saguaro_pwdc", $c_pass, time() + 7 * 24 * 3600, '/', $cookie_domain); // 1 week cookie expiration

logme("Checking autosticky status");
if (defined('AUTOSTICKY') && AUTOSTICKY) {
    $autosticky = preg_split("/,\s*/", AUTOSTICKY);
    if ($resto == 0) {
        if ($insertid % 1000000 == 0 || in_array($insertid, $autosticky))
            $sticky = true;
    }
}

if (!$resto) {
    if (!$is_sage && $log[$resto]['bumplimit'] < 1 && $log[$resto]['permasage'] < 1 && $log[$resto]['sticky'] < 1) { //Sage action is "processed" here
        $query = "UPDATE " . SQLLOG . " SET last='$insertid' WHERE no='$insertid' AND board='" . BOARD_DIR . "'"; //Bump
        $mysql->query($query);
    }
}

$static_rebuild = STATIC_REBUILD;

if (!$resto) { //Prune old posts off index. Archive if necessary
    logme("Before trim");
    require_once('inc/prune.php');
    
    if (ENABLE_ARCHIVE) {
        $archive_updated = update_archive();
    }
    
    if ($archive_updated !== true) { //Archive failed or was not enabled. Delete old posts.
        if (EXPIRE_NEGLECTED) {
            prune_old();
        }
    }
    
} else { //Cylical threads
    if ($log[$resto]['sticky'] == 2)
        prune_thread($resto);
}
logme("After trim");

if (PROCESSLIST && (time() > ($time + 7))) {
    $dump = $mysql->escape_string(serialize(array(
        'GET' => $_GET,
        'POST' => $_POST,
        'SERVER' => $_SERVER
    )));
    $mysql->query("INSERT INTO " . SQLPROFILING . " VALUES (connection_id(),'$dump','slow post')");
}

unset($my_log->cache, $log);

$deferred = false;
$deferred = ($resto) ? $my_log->update($resto, $static_rebuild) : $my_log->update($insertid, $static_rebuild); // update html
logme("Pages rebuilt (resto= $resto | insertid= $insertid | static_Rebuild = $static_rebuild");

// determine url to redirect to 
if ($noko && !$resto) {
    $redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $insertid;
} else if ($noko) {
    $redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $resto . '#' . $insertid;
} else {
    $redirect = PHP_SELF2_ABS;
}


if (API_ENABLED) {
    logme("Updating API.");
    require_once(CORE_DIR . "/api/apoi.php");
    $API    = new SaguaroAPI;
    $apiout = ($resto) ? $resto : $insertid;
    $API->formatThread($apiout, $resto);
}

require_once(CORE_DIR . "/page/head.php");
$head = new Head;

if (!$mes) $mes = "Post No.{$insertid} success!";

if ($deferred) {
    $temp = "<html><head><META HTTP-EQUIV=\"refresh\" content=\"5;URL=$redirect\"></head>";
    $temp .= "<body><h1>$mes<br>Your post may not appear immediately. <!--thread:$resto,no:$insertid --></h1></body></html>";
} else {
    $temp = "<html><head><META HTTP-EQUIV=\"refresh\" content=\"1;URL=$redirect\"></head>";
    $temp .= "<body><h1>$mes <!--thread:$resto,no:$insertid --></h1></body></html>";
}

$head->info['css']['raw'] = array("body { margin-top: 20%; text-align:center;} h1 {font-size:36pt;}");
echo $head->generate($noHead = true);
echo $temp;
unset($temp);