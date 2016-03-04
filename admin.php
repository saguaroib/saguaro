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

function delete_post($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1, $delhost = '') {
	$resno = $mysql->escape_string($resno);
    require_once(CORE_DIR . "/admin/delete.php");
    $remove = new Delete;
    $remove->targeted($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1, $delhost = '');
}

function error($mes) { //until error class is sorted out, this is in-house admin error
    $html = "<br><br><hr><br><br><div style='text-align:center;font-size:24px;font-color:blue;'>$mes<br><br><a href='" . PHP_ASELF_ABS . "'>" . S_RELOAD . "</a></div><br><br><hr>";
    echo $page->generate($html, true, false);
    die("</body></html>");
}

/* Main switch */
switch ($_GET['mode']) {
    case 'res':
        $html = $table->deleteTable($type = 'res', $_GET['no']);
		echo $page->generate($html, true);
        break;
    case 'ip' :
        $html = $table->deleteTable($type = 'ip', $_GET['no']);
		echo $page->generate($html, true);
        break;
    case 'ops':
        $html = $table->deleteTable($type = 'ops', 0);
		echo $page->generate($html, true);
        break;
    case 'staff':
        require_once(CORE_DIR . "/admin/staff.php");
        if ($_POST) Staff::process($_POST);
        $html = $table->staffTable();
        echo $page->generate($html, true, false);
        break;
    case 'adel':
        if (!valid('janitor')) error(S_NOPERM);
        delete_post($_GET['no'], 0, $_GET['imgonly'], 0, 1, 1, '');
        break;
    case 'ban':
        if (!valid('moderator')) error(S_NOPERM);
        require_once(CORE_DIR . "/admin/bans.php");
		$bans = new Banish;
        if ($_POST['no']) $bans->process($_POST);
		echo $bans->form($_GET);
        break;
    case 'rebuild':
		if (!valid("admin")) error(S_NOPERM);
        require_once(CORE_DIR . "/log/rebuild.php");
        rebuild(1);
        break;
    case 'reports':
        if ($_POST['no']) $getReport->clearNum($_POST['no']);
        $html = $table->reportTable();
        echo $page->generate($html, true, false);
        break;
    case 'news':
        if (!valid('admin')) error(S_NOPERM);
        require_once(CORE_DIR . "/admin/news.php");
        if ($_POST['update']) News::newsUpdate($_POST);
        $html = News::newsPanel();
		echo $page->generate($html, true, false); 
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
        $html = $table->deleteTable($type = 'all', 0);
		echo $page->generate($html, true, false);
        break;
}
?>
