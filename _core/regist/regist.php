<?php

/*

Eventually rewrite this.

*/

$host = $_SERVER['REMOTE_ADDR'];

$upfile_name = $_FILES["upfile"]["name"];
$upfile = $_FILES["upfile"]["tmp_name"];

require_once('process/upload.php'); //Check prereq conditions for post processing

global $my_log, $mysql, $path, $badstring, $badfile, $badip, $pwdc, $textonly;

require_once(CORE_DIR . "/regist/sanitize.php");
$sanitize = new Sanitize;

if ($pwd == PANEL_PASS)
    $admin = $pwd;
if ($admin != PANEL_PASS || !valid())
    $admin = '';
$mes = "";

if (valid('moderator')) {
    $moderator = 1;
    if (valid('manager'))
        $moderator = 3;
    if (valid('admin'))
        $moderator = 2;
}

if ($moderator) {
    if (isset($_POST['isSticky'])) {
        $stickied = 1;
        if (isset($_POST['eventSticky'])) //Experimental feature.
            $stickied = 2;
    }
    if (isset($_POST['isLocked']))
        $locked = 1;
}

if (!$upfile && !$resto) { // allow textonly threads for moderators!
    if ($moderator) //They have the permission anyway, might as well remove the query until the user class is done.
        $textonly = 1;
}

// time
$time = time();
$tim  = $time . substr(microtime(), 2, 3);

require_once('process/upload_file.php'); //Process the uploaded file.

//The last result number
$lastno = $mysql->result($mysql->query("select max(no) from " . SQLLOG), 0, 0);

// Number of log lines
if (!$result = $mysql->query("select no,ext,tim from " . SQLLOG . " where no<=" . ($lastno - LOG_MAX))) {
    echo S_SQLFAIL;
} else {
    while ($resrow = $mysql->fetch_row($result)) {
        list($dno, $dext, $dtim) = $resrow;
        if (!$mysql->query("delete from " . SQLLOG . " where no=" . $dno)) {
            echo S_SQLFAIL;
        }
        if ($dext) {
            if (is_file($path . $dtim . $dext))
                unlink($path . $dtim . $dext);
            if (is_file(THUMB_DIR . $dtim . 's.jpg'))
                unlink(THUMB_DIR . $dtim . 's.jpg');
        }
    }
    $mysql->free_result($result);
}

$find  = false;
$resto = (int) $resto;
if ($resto) {
    if (!$result = $mysql->query("select * from " . SQLLOG . " where root>0 and no=$resto")) {
        echo S_SQLFAIL;
    } else {
        $find = $mysql->fetch_row($result);
        $mysql->free_result($result);
    }
    if (!$find)
        error(S_NOTHREADERR, $dest);
}

if (!$name)
    $name = S_ANONAME;
if (!$com)
    $com = S_ANOTEXT;
if (!$sub)
    $sub = S_ANOTITLE;

if (!$resto && !$textonly && !is_file($dest) && !$moderator)
    error(S_NOPIC, $dest);
if (!$com && !is_file($dest) && !$moderator)
    error(S_NOTEXT, $dest);

// No, path, time, and url format
srand((double) microtime() * 1000000);

if ($pwd == "") {
    $pwd = $_COOKIE['saguaro_pass'];
    if ($pwd == "") {
        $pwd = rand();
        $pwd = substr($pwd, 0, 8);
    }
}

$c_pass = $pwd;
$c_email = $email;

$pass   = ($pwd) ? substr(md5($pwd), 2, 8) : "*";
$youbi  = array(
     S_SUN,
    S_MON,
    S_TUE,
    S_WED,
    S_THU,
    S_FRI,
    S_SAT
);
$yd     = $youbi[date("w", $time)];

$now = (SHOW_SECONDS == 1)  ? date("m/d/y", $time) . "(" . (string) $yd . ")" . date("H:i:s", $time) : date("m/d/y", $time) . "(" . (string) $yd . ")" . date("H:i", $time);

if (DISP_ID) {
    //$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
    //$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
    $color  = "inherit"; // Until unique IDs between threads get sorted out
    //Leave these quotes escaped for mysql
    $idhtml = "<span class=\"posteruid\" id=\"posterid\" style=\"background-color:" . $color . "; border-radius:10px;font-size:8pt;\" />";
    $mysql->escape_string($idhtml);

    if ($email && DISP_ID == 1) {
        $now .= " (ID:" . $idhtml . " Heaven </span>)";
    } else {
        if (!$resto) {
        //holy hell there has to be a better way to do this. i swear ill think of it soon
        $idsalt = $mysql->result($mysql->query("select max(no) from " . SQLLOG), 0, 0); 
        $idsalt = $idsalt + 1;
        } else {
            $idsalt = $resto;
        }
        $now .= " (ID:" . $idhtml . substr(crypt(md5($_SERVER["REMOTE_ADDR"] . PANEL_PASS . 'id' . date("Ymd", $time)), $idsalt), +3) . "</span>)";
    }
}

if (file_exists('geoiploc.php')) {
    require_once("geoiploc.php");
    $country = strtolower(getCountryFromIP($host, "CTRY"));
    $now .= " <img src='" . CSS_PATH . "/imgs/flags.png' class='f-$country' /> ";
}

if (strpos($email,'sage') !== false || strpos($email,'nokosage') !== false)
    $sageThis = true;

if (strpos($email,'nokosage') !== false || strpos($email,'noko') !== false)
    $noko = true;

$noko = true;
//Text sanitizing
//Text plastic surgery (rorororor)
$email = $sanitize->CleanStr($email, 0); //Don't allow moderators to fuck with this
$email = preg_replace("[\r\n]", "", $email);
$sub   = $sanitize->CleanStr($sub, 0); //Or this
$sub   = preg_replace("[\r\n]", "", $sub);
$url   = $sanitize->CleanStr($url, 0); //Or this
$url   = preg_replace("[\r\n]", "", $url);
$resto = $sanitize->CleanStr($resto, 0); //Or this
$resto = preg_replace("[\r\n]", "", $resto);
$com   = $sanitize->CleanStr($com, $moderator); //But they can with this.

$clean = $sanitize->process($name, $com, $sub, $email, $resto, $url, $dest, $moderator);

$sub = $clean['sub'];
$com = $clean['com'];
$email = $clean['email'];
$name = $clean['name'];

unset($clean);

require_once("tripcode.php"); //This DOES the trip processing.


if (USE_BBCODE) {
    require_once(CORE_DIR . '/general/text_process/bbcode.php');

    $bbcode = new BBCode;
    $com = $bbcode->format($com);
}

if (SPOILERS && $spoiler)
    $sub = "SPOILER<>" . $sub;

if ($moderator && isset($_POST['showCap'])) {
    if ($moderator == 1)
        $name = '<span class="cap moderator" >' . $name . ' ## Mod </span>';
    if ($moderator == 3)
        $name = '<span class="cap manager" >' . $name . ' ## Manager  </span>';
    if ($moderator == 2)
        $name = '<span class="cap admin" >' . $name . ' ## Admin </span>';
}

if (FORCED_ANON) {
    $name = S_ANONAME . " </span>$now<span>";
    $sub  = '';
    $now  = '';
}


$may_flood = ($moderator >= 1);

if (!$may_flood) {
    if ($com) {
        // Check for duplicate comments
        $query  = "select count(no)>0 from " . SQLLOG . " where com='" . $mysql->escape_string($com) . "' " . "and host='" . $mysql->escape_string($host) . "' " . "and time>" . ($time - RENZOKU_DUPE);
        $result = $mysql->query($query);
        if ($mysql->result($result, 0, 0))
            error(S_RENZOKU, $dest);
        $mysql->free_result($result);
    }

    if (!$has_image) {
        // Check for flood limit on replies
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ($time - RENZOKU) . " " . "and host='" . $mysql->escape_string($host) . "' and resto>0";
        $result = $mysql->query($query);
        if ($mysql->result($result, 0, 0))
            error(S_RENZOKU, $dest);
        $mysql->free_result($result);
    }

    if ($sageThis) {
        // Check flood limit on sage posts
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ($time - RENZOKU_SAGE) . " " . "and host='" . $mysql->escape_string($host) . "' and resto>0 and permasage=1";
        $result = $mysql->query($query);
        if ($mysql->result($result, 0, 0))
            error(S_RENZOKU, $dest);
        $mysql->free_result($result);
    }

    if (!$resto) {
        // Check flood limit on new threads
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ($time - RENZOKU3) . " " . "and host='" . $mysql->escape_string($host) . "' and root>0"; //root>0 == non-sticky
        $result = $mysql->query($query);
        if ($mysql->result($result, 0, 0))
            error(S_RENZOKU3, $dest);
        $mysql->free_result($result);
    }
}

// Upload processing
if ($has_image) {
    if (!$may_flood) {
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ($time - RENZOKU2) . " " . "and host='" . $mysql->escape_string($host) . "' and resto>0";
        $result = $mysql->query($query);
        if ($mysql->result($result, 0, 0))
            error(S_RENZOKU2, $dest);
        $mysql->free_result($result);
    }

    //Duplicate image check
    if (DUPE_CHECK) {
        $result = $mysql->query("select no,resto from " . SQLLOG . " where md5='$md5'");
        if ($mysql->num_rows($result)) {
            list($dupeno, $duperesto) = $mysql->fetch_row($result);
            if (!$duperesto)
                $duperesto = $dupeno;
            error('<a href="' . DATA_SERVER . BOARD_DIR . "/res/" . $duperesto . PHP_EXT . '#' . $dupeno . '">' . S_DUPE . '</a>', $dest);
        }
        $mysql->free_result($result);
    }
    
    //Thumbnail
    rename($dest, $path . $tim . $ext);
    if (USE_THUMB) { //We'll still make the thumbnail even if its a spoiler image for user extensions.
        require_once("thumb.php");
        $tn_name = thumb($path, $tim, $ext, $resto);
        if (!$tn_name && $ext != ".pdf") {
            error(S_UNUSUAL);
        }
    }
}

$rootqu = $resto ? "0" : "now()";

if ($stickied)
    $rootqu = '20270727070707';

//Bump processing
if ($resto) { 
    $countres = $mysql->result("SELECt COUNT(no) FROM " . SQLLOG . " where resto=" . $resto, 0, 0);
    $stat = $mysql->fetch_assoc("SELECT sticky,permasage FROM " . SQLLOG . " WHERE no=" . $resto);
    if (!$sageThis && $countres < MAX_RES && !$stat['sticky'] && !$stat['permasage']) //|| ($admin && $age && $sticky < "0"))
        $mysql->query("UPDATE " . SQLLOG . " SET root=now() WHERE  no='$resto'"); //Bump
}

//Main insert
$query = "INSERT INTO " . SQLLOG . " (now,name,email,sub,com,host,pwd,ext,w,h,tn_w,tn_h,tim,time,md5,fsize,fname,sticky,permasage,locked,root,resto) VALUES (" . "'" . $now . "',"
 . "'" . $mysql->escape_string($name) . "'," 
 . "'" . $mysql->escape_string($email) . "',"
 . "'" . $mysql->escape_string($sub) . "'," 
 . "'" . $mysql->escape_string($com) . "'," 
 . "'" . $mysql->escape_string($host) . "'," 
 . "'" . $mysql->escape_string($pass) . "'," 
 . "'" . $ext . "',"
 . (int) $W . ","
 . (int) $H . ","
 . (int) $TN_W . "," 
 . (int) $TN_H . "," 
 . "'" . $tim . "',"
 . (int) $time . ","
 . "'" . $md5 . "',"
 . (int) $fsize . ","
 . "'" . $mysql->escape_string($upfile_name) . "',"
 . (int) $stickied . ","
 . (int) $permasage . ","
 . (int) $locked . ","
 . $rootqu . ","
 . (int) $mysql->escape_string($resto) . ")";

if (!$result = $mysql->query($query)) {
    echo E_REGFAILED;
} //post registration

$cookie_domain = '.' . SITE_ROOT . '';

//Begin cookies
/*if ($c_name) //Name
    setrawcookie("saguaro_name", rawurlencode($c_name), time() + ($c_name ? (7 * 24 * 3600) : -3600), '/', $cookie_domain);*/

if (($c_email != "sage") && ($c_email != "age")) //Email
    setcookie("saguaro_email", $c_email, time() + ($c_email ? (7 * 24 * 3600) : -3600), '/', $cookie_domain); // 1 week cookie expiration

//Pass
setcookie("saguaro_pass", $c_pass, time() + 7 * 24 * 3600, '/', $cookie_domain); // 1 week cookie expiration

//End cookies

if (!$resto) {
    require_once('prune_old.php');
    prune_old();
} else {//Event stickies
    $eventStick = $mysql->query("SELECT sticky FROM " . SQLLOG . " WHERE no='$resto' AND sticky=2");
    if ($mysql->num_rows($eventStick) > 0) {
        require_once('prune_old.php');
        pruneThread($resto);
    }
    $mysql->free_result($eventStick);
}

$static_rebuild = defined("STATIC_REBUILD") && (STATIC_REBUILD == 1);

//Finding the last entry number
if (!$result = $mysql->query("select max(no) from " . SQLLOG)) {
    echo S_SQLFAIL;
}
$hacky    = $mysql->fetch_array($result);
$insertid = (int) $hacky[0];
$mysql->free_result($result);

$deferred = false;
// update html
if ($resto)
    $deferred = $my_log->update($resto, $static_rebuild);
else 
    $deferred = $my_log->update($insertid, $static_rebuild);

if ($noko && !$resto) {
    $redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $insertid . PHP_EXT;
} else if ($noko) {
    $redirect = DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $resto . PHP_EXT . '#' . $insertid;
} else {
    $redirect = PHP_SELF2_ABS;
}

if ($deferred) {
    echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"2;URL=$redirect\"></head>";
    echo "<body>$mes " . S_SCRCHANGE . "<br>Your post may not appear immediately.<!-- thread:$resto,no:$insertid --></body></html>";
} else {
    echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"1;URL=$redirect\"></head>";
    echo "<body>$mes " . S_SCRCHANGE . "<!-- thread:$resto,no:$insertid --></body></html>";
}


?>
