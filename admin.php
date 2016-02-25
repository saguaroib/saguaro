<?php

require('config.php');

require_once(CORE_DIR . "/mysql/mysql.php");	//Init SQL
$mysql = new SaguaroMySQL;
$mysql->init();

require_once(CORE_DIR . "/admin/login.php");	//First line of security. Die script if user isn't logged in
$login = new Login;
$login->auth();

require_once(CORE_DIR . "/page/page.php");		//Load page class
$page = new Page;
$page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - Management Panel";

require_once(CORE_DIR . "/log/log.php");		//Load and initialize Log.
$my_log = new Log;

require_once(CORE_DIR . "/admin/tables.php");	//Load post tables
$table = new Table;

require_once(CORE_DIR . "/admin/report.php");	//Load report queue
$getReport = new Report;

extract($_POST, EXTR_SKIP);

function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/valid.php");
    $valid = new Valid;
    return $valid->verify($action);
}

function delete_post($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1) {
    require_once(CORE_DIR . "/admin/delete.php");
    $remove = new Delete;
    $remove->targeted($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1);
}

function error($mes) { //until error class is sorted out, this is in-house admin error
    echo "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:blue;'>$mes<br><br><a href='" . PHP_ASELF_ABS . "'>" . S_RELOAD . "</a></div><br><br><hr>";
    die("</body></html>");
}

/* Main switch */
switch ($_GET['mode']) {
    case 'res':
        $html = $table->display($type = 'res', $_GET['no']);
		echo $page->generate($html, true);
        break;
    case 'ip' :
        $html = $table->display($type = 'ip', $_GET['no']);
		echo $page->generate($html, true);
        break;
    case 'ops':
        $html = $table->display($type = 'ops', 0);
		echo $page->generate($html, true);
        break;
    case 'staff':
        head(0);
        if (!valid('admin')) 
            error(S_NOPERM);
        require_once(CORE_DIR . "/admin/staff.php");
        $staff = new Staff;
        echo $staff->getStaff();
        if (isset($_POST['user']) && isset($_POST['pwd1']) && isset($_POST['pwd2']) && isset($_POST['action']))
            $staff->addStaff($_POST['user'], $_POST['pwd1'], $_POST['pwd2'], $_POST['action']);
        break;
    case 'adel':
        if (!valid('janitor')) error(S_NOPERM);
        delete_post($no, 0, $_GET['imgonly'], 0, 1, 1);
        echo '<meta http-equiv="refresh" content="0; url=' . PHP_ASELF_ABS . '?mode=' . $_GET['refer'] . '" />';
        break;
    case 'ban':
        if (!valid('moderator')) error(S_NOPERM);
        require_once(CORE_DIR . "/admin/bans.php");
        if ($no) Banish::process($no, $ip, $length, $global, $reason, $pubreason);
        Banish::form($_GET['no']);
        break;
    case 'rebuild':
        require_once(CORE_DIR . "/log/rebuild.php");
        rebuild(1);
        break;
    case 'reports':
        head(0);
        if (isset($_GET['no']))
            $getReport->reportClear($_GET['no']);
        $active    = $getReport->reportGetAllBoard();
        echo $getReport->reportList();
        break;
    case 'news':
        head(0);
        if (!valid('admin'))
            error(S_NOPERM);
        require_once(CORE_DIR . "/admin/news.php");
        $news = new News; //lol
        if (isset($_POST['update']) && isset($_POST['file']) || isset($_POST['boardlist']))
            $news->newsUpdate($_POST['update'], $_POST['file']);
        echo $news->newsPanel();
        break;
    case 'more':
        $html = $table->moreInfo($_GET['no']);
		echo $page->generate($html, true, false);
        break;
    case 'modify':
        require_once(CORE_DIR . "/admin/modify.php");
        $modify = new Modify;
        echo $modify->mod($_GET['no'], $_GET['action']);
        break;
    case 'logout':
        setcookie('saguaro_apass', '0', 1);
        setcookie('saguaro_auser', '0', 1);
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
        break;
    default:
        $html = $table->display($type = 'all', 0);
		echo $page->generate($html, true, false);
        break;
}
?>
