<?php

require('config.php');

require_once(CORE_DIR . "/mysql/mysql.php");
$mysql = new SaguaroMySQL;
$mysql->init();
$con = $mysql->connection;

if (!function_exists(mysql_call)) {
    function mysql_call($query) {
        global $mysql;
        return $mysql->query($query);
    }
}

//Load and initialize Log.
require_once(CORE_DIR . "/log/log.php");
$my_log = new Log;

extract($_POST);

//Display head.
function head() {
    require_once(CORE_DIR . "/general/head.php");
    $head = new Head;
    return $head->generate();
}

//Admin form
function aform(&$post, $resno, $admin = "")
{
    require_once(CORE_DIR . "/postform.php");
    $postform = new PostForm;
    $post .= "<div id='adminForm' style='display:none; align:center;' />" . $postform->format($resno, $admin) . "</div><form action='" . PHP_ASELF . "' method='post'>
    <input type=hidden name=admin value=del checked>";
    echo $post;
}

function isAuthed($pass) {
    $good->auth($pass);
    if (isset($_POST['usernm']) && isset($_POST['passwd']))
        $good->doLogin($_POST['usernm'], $_POST['passwd']);
}

/* Admin deletion */
function admindel($pass)
{
    global $path, $onlyimgdel;
    require_once(CORE_DIR . "/admin/postInfo.php");
    $list = new DelTable;
    $list->displayTable();
}

function valid($action = 'moderator', $no = 0)
{
    require_once(CORE_DIR . "/admin/validate.php");
    $validate = new Validation;
    $allowed  = $validate->verify($action);
    return $allowed;
}

function delete_post($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1)
{
// deletes a post from the database
// imgonly: whether to just delete the file or to delete from the database as well
// automatic: always delete regardless of password/admin (for self-pruning)
// children: whether to delete just the parent post of a thread or also delete the children
// die: whether to die on error
// careful, setting children to 0 could leave orphaned posts.    
    require_once(CORE_DIR . "/log/log.php");
    require_once(CORE_DIR . "/admin/delpost.php");
    $remove = new DeletePost;
    $remove->targeted($resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1);
}

/* Main switch */
switch ($_GET['mode']) {
    case 'admin':
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
        break;
    case 'more':
        require_once(CORE_DIR . "/admin/postInfo.php");
        $list = new DelTable;
        echo $list->moreInfo($_GET['no']);
        break;
    case 'logout':
        setcookie('saguaro_apass', '0', 1);
        setcookie('saguaro_auser', '0', 1);
        echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
        break;
    case 'ban':
		echo head();
        require_once(CORE_DIR . "/admin/banish.php");

		$banish = new Banish;
		if ($banish->checkBan($_SERVER['REMOTE_ADDR'])) {
			$banish->postOptions($no, $_SERVER['REMOTE_ADDR'], $_POST['banlength'], $_POST['banType'], $_POST['perma'], $_POST['pubreason'], $_POST['staffnote'], $_POST['custmess'], $_POST['showbanmess'], $_POST['afterban']);
        //gee i hope nobody saw this
		}
		$banish->afterBan;
		break;
	case 'reports':
		require_once(CORE_DIR . "/admin/report.php");
		$getReport = new Report;
		$active = $getReport->get_all_reports_board();
		oldvalid($pass);
		$getReport->display_list();
		break;
    case 'rebuild':
		require_once(CORE_DIR . "/log/rebuild.php");
        rebuild();
        break;
    case 'rebuildall':
		require_once(CORE_DIR . "/log/rebuild.php");
        rebuild(1);
        break;
    case "modipost":
        require_once(CORE_DIR . "/admin/modifyPost.php");
        $modify = new Modify;
        echo $modify->mod($_GET['no'], $_GET['action']);
        break;
    default:
        require_once(CORE_DIR . "/admin/login.php");
        $good = new Login;
        $good->auth($pass);
        aform($post, $res, 1);
        admindel($pass);
        die("</body></html>");
        break;
}
?>
