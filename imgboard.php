<?php
/*
Saguaro Imageboard by team saguaro http://github.com/spootTheLousy/saguaro v1.0
Founded by spoot of http://saguaroimgboard.tk
Contributors: RePod, !KNs1o0VDv6, Glas, Anonymous of vchan, Apogate
*/
session_start();

require "config.php";
require_once(CORE_DIR . "/lang/language.php");

require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

require_once(CORE_DIR . "/boards/load.php");
$loader = new BoardLoader;
$loader->getTable();

$host = $mysql->escape_string($_SERVER['REMOTE_ADDR']); //Get this once here at the root instead of 300 different times. Use globally.
$path = realpath("./") . '/' . IMG_DIR;

extract($_POST, EXTR_SKIP);
extract($_GET, EXTR_SKIP);
extract($_COOKIE, EXTR_SKIP);

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

/*-----------Main-------------*/
switch ($mode) {
    case 'regist': //Making a post
        $my_log->update_cache();
        $log = $my_log->cache;
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
    /*case 'admin':
        /*if (!valid('janitor')) {
            echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2_ABS . "'>";
        }
        require_once(CORE_DIR . "/admin/admin.php");
        $admin = new AdminRoot;
        echo $admin->init();
        break;*/
    case 'usrdel': //User deleting a post
        require_once(CORE_DIR . "/admin/delete.php");
        $del = new Delete;
        $del->userDel();
        break;
    case 'arc':
        if (ENABLE_ARCHIVE) {
            require_once(CORE_DIR . "/log/archive.php");
            $dw = new SaguaroArchive;
            echo $dw->generate();
        } else {
            error(S_NOARCHIVE);
        }
        break;
    default:
        if ($_GET['res']) { //Jump to any post
            require_once(CORE_DIR . "/general/resredir.php");
            echo "<META HTTP-EQUIV='refresh' content='5;URL=" . PHP_SELF2_ABS . "'>";
        } else { //Update index.
            //echo S_SCRCHANGE;
            $my_log->update();
            echo "<META HTTP-EQUIV='refresh' content='0;URL=" . PHP_SELF2_ABS . "'>";
        }
}
?>
