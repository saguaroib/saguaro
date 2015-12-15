<?php

require('config.php');

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();

require_once(CORE_DIR . "/admin/login.php");
$login = new Login;
$login->auth();

//Load and initialize Log.
require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

//Load post table
require_once(CORE_DIR . "/admin/tables.php");
$table = new Table;

//Load report queue
require_once(CORE_DIR . "/admin/report.php");
$getReport = new Report;

extract($_POST, EXTR_SKIP);

//Display head.
function head($noHead) {
    require_once(CORE_DIR . "/page/head.php");
    $head = new Head;
    $head->info['page']['title'] = "/" . BOARD_DIR . "/ - Management Panel";
    echo $head->generateAdmin($noHead);
}

//Admin form
function aform(&$post, $resno, $admin = "") {
    require_once(CORE_DIR . "/postform.php");
    $postform = new PostForm;
    $post .= "<div id='adminForm' style='display:none; align:center;' />" . $postform->format($resno, $admin) . "</div>";
    echo $post;
}

function valid($action = 'moderator', $no = 0) {
    require_once(CORE_DIR . "/admin/valid.php");
    $valid = new Valid;
    return $valid->verify($action);
}

function delete_post($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1) {
    require_once(CORE_DIR . "/log/log.php");
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
        head(0);
        aform($post = '', 0, 1);
        $table->display($type = 'res', $_GET['no']);
        break;
    case 'all':
        head(0);
        aform($post = '', 0, 1);
        $table->display($type = 'all', 0);
        break;
    case 'ip' :
        head(0);
        aform($post = '', 0, 1);
        $table->display($type = 'ip', $_GET['no']);
        break;
    case 'ops':
        head(0);
        aform($post = '', 0, 1);
        $table->display($type = 'ops', 0);
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
        if (!valid('janitor'))
                error(S_NOPERM);
        $no = $mysql->escape_string($_GET['no']);
        $imonly = ($_GET['imgonly'] == '1') ? 0 : 1;
        delete_post($no, 0, $imonly, 0,1,1);
        echo '<meta http-equiv="refresh" content="0; url=' . PHP_ASELF_ABS . '?mode=' . $_GET['refer'] . '" />';
        break;
    case 'ban':
        if (!valid('moderator'))
            error(S_NOPERM);
        require_once(CORE_DIR . "/admin/bans.php");
        $banish = new Banish;
        if (isset($no));
            $banish->postOptions($no, $ip, $banlength, $banType, $perma, $pubreason, $staffnote, $custmess, $showbanmess, $afterban);
        $banish->form($_GET['no']);
        break;
    case 'more':
        echo $table->moreInfo($_GET['no']);
        break;
    case "modify":
        require_once(CORE_DIR . "/admin/modify.php");
        $modify = new Modify;
        echo $modify->mod($_GET['no'], $_GET['action']);
        break;
    case 'logout':
        setcookie('saguaro_apass', '0', 1);
        setcookie('saguaro_auser', '0', 1);
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
        break;
    case 'rebuild':
        require_once(CORE_DIR . "/log/rebuild.php");
        rebuild(1);
        break;
    case 'reports':
        head(0);
        require_once(CORE_DIR . "/admin/report.php");
        $getReport = new Report;
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
    default:
        head(0);
        aform($post = '', 0, 1);
        $table->display($type = 'all', 0);
        break;
}
?>
