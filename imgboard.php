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

require("config.php");
require_once(CORE_DIR . "/lang/language.php");

require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

$host = $mysql->escape_string($_SERVER['REMOTE_ADDR']); //Get this once here at the root instead of 300 different times. Use globally.
$path = realpath("./") . '/' . IMG_DIR;

extract($_POST, EXTR_SKIP); //We're almost free

ignore_user_abort(TRUE);

// check whether the current user can perform $action (on $no, for some actions)
// board-level access is cached in $valid_cache.
function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/valid.php");
    $validate = new Valid;
    return $validate->verify($action);
}

function logme($mes) {
    if (defined("PROFILING") && PROFILING) {
        require_once(CORE_DIR . "/profiling/logging.php");
        $log = new Profiling;
        $log->log($mes);
    }
    return;
}

function error($mes, $dest = null, $fancy = 0) {
    require_once(CORE_DIR . "/general/error.php");
    $error = new Error();
    $error->format($mes, $dest, $fancy);
}

$mode = (isset($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];

/*-----------Main-------------*/
switch ($mode) {
    case 'regist': //Making a post
        $my_log->update_cache();
        require_once(CORE_DIR . "/regist/regist.php"); // $name, $email, $sub, $com, $url, $pwd, $resto
        break;
    case 'report': //Filing a report
        require_once(CORE_DIR . "/admin/reports/report.php");
        $report = new Report;
        echo $report->init();
        break;
    case 'banned': //You are banned/warned! screen
        require_once(CORE_DIR . "/admin/bans/banned.php");
        $ban  = new BanishScreen;
        echo $ban->init($host);
        break;
    case 'usrdel': //User deleting a post
        require_once(CORE_DIR . "/admin/delete.php");
        $del = new Delete;
        $del->userDel();
        break;
    case 'ping':
        echo "pong!";
        break;
    default:
        if ($_GET['res']) { //Jump to any post based on given post #
            require_once(CORE_DIR . "/general/resredir.php");
            echo "<META HTTP-EQUIV='refresh' content='5;URL=" . PHP_SELF2_ABS . "'>";
        } else { //Update index.
            $my_log->update();
            echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2_ABS . "'>";
        }
}
?>
?>
