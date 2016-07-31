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

require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

$host = $mysql->escape_string($_SERVER['REMOTE_ADDR']); //Get this once here at the root instead of 300 different times. Use globally.

extract($_POST, EXTR_SKIP);
extract($_GET, EXTR_SKIP);
extract($_COOKIE, EXTR_SKIP);

$path = realpath("./") . '/' . IMG_DIR;
ignore_user_abort(TRUE);

// check whether the current user can perform $action (on $no, for some actions)
// board-level access is cached in $valid_cache.
function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/valid.php");
    $validate = new Valid;
    return $validate->verify($action);
}

function error($mes, $dest, $fancy = 0) {
    require_once(CORE_DIR . "/general/error.php");
    $error = new Error();
    $error->format($mes, $dest, $fancy);
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
    case 'catalog':
        header("Location: catalog.html"); //let go of the past
        break;
    case 'usrdel':
        require_once(CORE_DIR . "/admin/delete.php");
        $del = new SaguaroDelete;
        $del->userDel();
        break;
    default:
        if ($res) {
            require_once(CORE_DIR . "/general/resredir.php");
            echo "<META HTTP-EQUIV='refresh' content='10;URL=" . PHP_SELF2_ABS . "'>";
        } else {
            echo S_SCRCHANGE;
            $my_log->update();
            echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2_ABS . "'>";
        }
}
?>
