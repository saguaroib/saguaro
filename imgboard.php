<?php
/*
=================================
===Saguaro Imageboard Software===
=================================
>>1.0
http://saguaroimgboard.tk/download/
the above link will have the latest version.

This is a branch off of futallaby and is currently in development because
I felt like doing this and have always prefered imageboards to phpbb clones.

Special thanks to !KNs1o0VDv6, Glas, Anonymous from vchan, RePod, and anyone who actually uses this.
If you need help you can reach me at spoot@saguaroimgboard.tk
or http://saguaroimgboard.tk/sug/
or if you would like to help development and have php experience.
If you need help setting saguaro up, check http://saguaroimgboard.tk/suprt/
Remember to look through older threads and see if your problem wasn't solved already!

*/

require "config.php";
session_start();

extract($_POST, EXTR_SKIP);
extract($_GET, EXTR_SKIP);
extract($_COOKIE, EXTR_SKIP);

$path = realpath("./") . '/' . IMG_DIR;
ignore_user_abort(TRUE);

$badstring = ["nimp.org"]; // Refused text. Currently unused by Regist.
$badfile = ["dummy", "dummy2"]; //Refused files (md5 hashes). Currently unused by Regist.

function mysql_call($query) {
    $ret = mysql_query($query);
    if (!$ret) {
    if (DEBUG_MODE) {
            echo "Error on query: " . $query . "<br />";
            echo mysql_error() . "<br />";
        } else {
            echo "MySQL error!<br />";
        }
    }
    return $ret;
}

//check for SQL table existance
$con  = mysql_connect(SQLHOST, SQLUSER, SQLPASS);

if (!$con) {
    echo S_SQLCONF; //unable to login to server (wrong user/pass?)
    exit;
}

if (!mysql_select_db(SQLDB, $con)) { echo S_SQLDBSF; } //Attempts to select the working database.

//Log
require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

// check whether the current user can perform $action (on $no, for some actions)
// board-level access is cached in $valid_cache.
function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/validate.php");

    $validate = new Validation;
    return $validate->verify($action);
}

function error($mes, $dest = '', $fancy = 0) {
    require_once(CORE_DIR . "/general/head.php");
    $head = new Head; $head = $head->generate();

    global $path;
    $upfile_name = $_FILES["upfile"]["name"];
    if (is_file($dest))
        unlink($dest);
    $dat .= $head;
    echo $dat;
    if ($mes == S_BADHOST) {
        die("<html><head><meta http-equiv='refresh' content='0; url=banned.php'></head></html>");
    } elseif (!$fancy) {
        echo "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:#blue'>$mes<br><br><a href='" . PHP_SELF2_ABS . "'>" . S_RELOAD . "</a></div><br><br><hr>";
        die("</body></html>");
    }
}

/* user image deletion */
function usrdel($no, $pwd) {
    global $path, $pwdc, $onlyimgdel;
    require_once(CORE_DIR . "/admin/delpost.php");

    $del = new DeletePost;
    $del->userDel($no, $pwd);
}

/*-----------Main-------------*/
switch ($mode) {
    case 'regist':
        require_once(CORE_DIR . "/regist/regist.php"); // $name, $email, $sub, $com, $url, $pwd, $resto
        break;
    case 'report':
        require_once(CORE_DIR . "/admin/report.php");
        $report = new Report;
        $report->process();
        break;
    case 'usrdel':
        usrdel($no, $pwd);
    default:
        if ($res) {
            require_once(CORE_DIR . "/general/resredir.php");
            echo "<META HTTP-EQUIV='refresh' content='10;URL=" . PHP_SELF2_ABS . "'>";
        } else {
            echo "Updating index...\n";
            $my_log->update();
            echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2_ABS . "'>";
        }
}
?>
