<?php

require( 'config.php' );

$con = mysql_connect( SQLHOST, SQLUSER, SQLPASS );

if ( !$con ) {
    echo S_SQLCONF; //unable to connect to DB (wrong user/pass?)
    exit;
}

$db_id = mysql_select_db( SQLDB, $con );
if ( !$db_id ) {
    echo S_SQLDBSF;
}

function postinfo( $no ) {
    if ( !valid( 'moderator' ) )
        die( 'AUTOBANMENOW - you' );
    
    $dat = '';
    $titlepart .= '/' . BOARD_DIR . '/ - ' . TITLE . '';
    $dat .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="jp"><head>
<meta name="description" content="' . S_DESCR . '"/>
<meta http-equiv="content-type"  content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- meta HTTP-EQUIV="pragma" CONTENT="no-cache" -->
<link REL="SHORTCUT ICON" HREF="/favicon.ico">
<link rel="stylesheet" type="text/css" href="' . CSS_PATH . CSS1 . '" title="Saguaba" />
<link rel="alternate stylesheet" type="text/css" media="screen"  href="' . CSS_PATH . CSS2 . '" title="Sagurichan"/>
<link rel="alternate stylesheet" type="text/css" media="screen"  href="' . CSS_PATH . CSS3 . '" title="Tomorrow" />
<link rel="alternate stylesheet" type="text/css" media="screen"  href="' . CSS_PATH . CSS4 . '" title="Burichan"/>
<script src="' . JS_PATH . '/jquery.min.js" type="text/javascript"></script>
<script src="' . JS_PATH . '/styleswitch.js" type="text/javascript"></script>
<script src="' . JS_PATH . '/main.js" type="text/javascript"></script>
<title>' . TITLE . '</title>';
    
    $dat .= "</head>
<body>" . $titlebar . "
<span class='boardlist'>" . file_get_contents( BOARDLIST ) . " </span>
<span class='adminbar'>
[<a href='" . HOME . "' target='_top'>" . S_HOME . "</a>]
[<a href='" . PHP_SELF_ABS . "?mode=admin' >" . S_ADMIN . "</a>]
</span><span class='delsettings' style='float:right;'/></span>
<div class='logo'>" . $titlepart . "</div>
<a href='#top' /></a>
<div class='headsub' >" . S_HEADSUB . "</div><hr />";
    
    if ( !$result = mysql_query( "SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'" ) )
        echo S_SQLFAIL;
    $row = mysql_fetch_row( $result );
    
    list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $resto, $board,  ) = $row;
    
    $dat .= "<table border='solid black 2px' border-collapse='collapse' />";
    /*if ( $resto == 0) {
    
    $dat .= "<tr><td>Post number:</td><td><b>OP POST(<a href='" . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT . "#" . $no . "'/>$no</a>)</b></td></tr>";
    } else {
    $dat .= "<tr><td>Post number:</td><td><a href='" . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT . "#" . $no . "'/>$no</a></td></tr>
    ";
    }*/
    $dat .= "<tr><td>Name:</td><td>$name</td></tr>
  <tr><td>Date:</td><td>$now</td></tr>
  <tr><td>IP</td><td>$host</td></tr>
  <tr><td>Comment:</td><td>$com</td></tr>
  <tr><td>MD5:</td><td>$md5</td></tr>
  <tr><td>File</td>";
    
    $dat .= "";
    
    if ( isset( $_REQUEST['test'] ) )
        echo 'fuark';
    
    if ( $w && $h ) {
        $hasimg = 1;
        $dat .= "<td><img width='" . MAX_W . "' height='" . MAX_H . "' src='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "'/></td></tr>
		<tr><td>Thumbnail:</td><td><img width='" . $tn_w . "' height='" . $tn_h . "' src='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg" . "'/></td></tr>
		<tr><td>Link to file | Link to thumbnail:</td><td><a href='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "' target='_blank' />Image</a> | <a href='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg' target='_blank' />Thumb</a></td></tr>";
    } else
        $dat .= "<td>No file</td></tr>";
    
    //<form action='admin.php'/><input type='submit' name='mode' value='test' /></form>
    
    $dat .= "<tr><form action='admin.php' <td>Delete:</td><td><br />
	<input type='hidden' name='mode' value='delete' />
	<input type='hidden' name='no' value='$no' />
    <input type='submit' name='action' value='This post' /><br />
    <input type='submit' name='action' value='Image only' /><br />
    <input type='submit' name='action' value='All by IP' /><br /></td></tr></table></form>";
    
    $dat .= "<br /><br /><table><form action='" . DATA_SERVER . BOARD_DIR . "/admin.php' />
	<tr><td>Action</td><td><td><select name='mode' />
    <option value='sticky' />Sticky</option>
    <option value='lock' />Lock</option>
    <option value='permasage' />Permasage</option>
    <option value='unsticky' />Unsticky</option>
    <option value='unlock' />Unlock</option>
    </select></td><td><input type='hidden' name='no' value='$no' /><input type='submit' value='Submit'></tr></table></form>";
    
    Echo $dat;
    echo '<br/ > [<a href="' . PHP_SELF . '?mode=admin" />Return</a>]';
    
}

if ( !function_exists( valid ) ) {
    // check whether the current user can perform $action (on $no, for some actions)
    // board-level access is cached in $valid_cache.
    function valid( $action = 'moderator', $no = 0 ) {
        static $valid_cache; // the access level of the user
        $access_level = array(
             'none' => 0,
            'janitor' => 1,
            'janitor_this_board' => 2,
            'moderator' => 5,
            'manager' => 10,
            'admin' => 20 
        );
        if ( !isset( $valid_cache ) ) {
            $valid_cache = $access_level['none'];
            if ( isset( $_COOKIE['saguaro_auser'] ) && isset( $_COOKIE['saguaro_apass'] ) ) {
                $user = mysql_real_escape_string( $_COOKIE['saguaro_auser'] );
                $pass = mysql_real_escape_string( $_COOKIE['saguaro_apass'] );
            }
            if ( $user && $pass ) {
                $result = mysql_call( "SELECT allowed,denied FROM " . SQLMODSLOG . " WHERE user='$user' and password='$pass'" );
                list( $allow, $deny ) = mysql_fetch_row( $result );
                mysql_free_result( $result );
                if ( $allow ) {
                    $allows             = explode( ',', $allow );
                    $seen_janitor_token = false;
                    // each token can increase the access level,
                    // except that we only know that they're a moderator or a janitor for another board
                    // AFTER we read all the tokens
                    foreach ( $allows as $token ) {
                        if ( $token == 'janitor' )
                            $seen_janitor_token = true;
                        /*  else if ( $token == 'manager' && $valid_cache < $access_level['manager'] )
                        $valid_cache = $access_level['manager'];*/
                        else if ( $token == 'admin' && $valid_cache < $access_level['admin'] )
                            $valid_cache = $access_level['admin'];
                        else if ( ( $token == BOARD_DIR || $token == 'all' ) && $valid_cache < $access_level['janitor_this_board'] )
                            $valid_cache = $access_level['janitor_this_board']; // or could be moderator, will be increased in next step
                    }
                    // now we can set moderator or janitor status 
                    if ( !$seen_janitor_token ) {
                        if ( $valid_cache < $access_level['moderator'] )
                            $valid_cache = $access_level['moderator'];
                    } else {
                        if ( $valid_cache < $access_level['janitor'] )
                            $valid_cache = $access_level['janitor'];
                    }
                    if ( $deny ) {
                        $denies = explode( ',', $deny );
                        if ( in_array( BOARD_DIR, $denies ) ) {
                            $valid_cache = $access_level['none'];
                        }
                    }
                }
            }
        }
        switch ( $action ) {
            case 'moderator':
                return $valid_cache >= $access_level['moderator'];
            case 'admin':
                return $valid_cache >= $access_level['admin'];
            case 'textonly':
                return $valid_cache >= $access_level['moderator'];
            case 'janitor_board':
                return $valid_cache >= $access_level['janitor'];
            /*case 'manager':
            return $valid_cache >= $access_level['manager'];*/
            case 'delete':
                if ( $valid_cache >= $access_level['janitor_this_board'] ) {
                    return true;
                }
                // if they're a janitor on another board, check for illegal post unlock			
                else if ( $valid_cache >= $access_level['janitor'] ) {
                    $query         = mysql_call( "SELECT COUNT(*) from reports WHERE board='" . BOARD_DIR . "' AND no=$no AND cat=2" );
                    $illegal_count = mysql_result( $query, 0, 0 );
                    mysql_free_result( $query );
                    return $illegal_count >= 3;
                }
            case 'reportflood':
                return $valid_cache >= $access_level['janitor'];
            case 'floodbypass':
                return $valid_cache >= $access_level['moderator'];
            default: // unsupported action
                return false;
        }
    }
}
if ( !function_exists( mysql_call ) ) {
    function mysql_call( $query ) {
        $ret = mysql_query( $query );
        if ( !$ret ) {
            echo "Error on query: " . $query . "<br />";
            echo mysql_error() . "<br />";
        }
        return $ret;
    }
}


/*
function ban($no) {

$placedOn = time();
$query    = mysql_call( "SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$ip' AND banlength != 0" );
switch ( $banlength ) {
case 'warn':
$banset = '100';
break;
case '3hr':
$banset = '1';
break;
case '3day':
$banset = '2';
break;
case '1wk':
$banset = '3';
break;
case '1mon':
$banset = '4';
break;
case 'perma':
$banset = '-1';
break;
default:
//Sure is 2007 around here
$banset = '9001';
}
if ( mysql_num_rows( $query ) == 0 ) {
$sql = "INSERT INTO " . SQLBANLOG . " (ip, pubreason, staffreason, banlength, placedOn, board) VALUES ('$ip', '$pubreason', '$staffreason', '$banset', '$placedOn', '" . BOARD_DIR . "')";

if ( mysql_call( $sql ) ) {
if ( $banset == '100' ) {
echo "Warned " . $ip . " for public reason: <br /><b> " . $pubreason . " </b><br />";
echo "Logged private reason: <br /><b> " . $staffreason . " </b>";
} else {
echo "Banned (" . $banlength . ") " . $ip . " for public reason: <br /><b> " . $pubreason . " </b><br />";
echo "Logged private reason: <br /><b> " . $staffreason . " </b>";
}
} else {
echo "ERROR: Could not execute $sql. " . mysql_error();
}
} else {
echo "This IP is already banned!";
}
mysql_free_result( $query );
} else {
die( 'You do not have permission to do that! IP: ' . $_SERVER['REMOTE_ADDR'] . " logged." );
}
*/

/* Main switch */
if ( valid( 'moderator' ) ) {
    switch ( $_GET['mode'] ) {
        case 'lock':
            $no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Locking thread $no";
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "\">";
            break;
        case 'permasage':
            $no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET permasage='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Permasaging $no";
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "\">";
            break;
        case 'sticky':
            $no      = $_GET['no'];
            $rootnum = "2027-07-07 00:00:00";
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='1' , root='" . $rootnum . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Stickying thread " . $no;
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "\">";
            break;
        case 'unlock':
            $no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='0' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Unlocking thread $no";
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "\">";
            break;
        case 'unsticky':
            $no      = $_GET['no'];
            $rootnum = date( 'Y-m-d G:i:s' );
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='0' , root='" . $rootnum . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Unstickying thread $no";
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "\">";
            break;
        case 'delete':
            include( 'imgboard.php' );
            $no     = $_GET['no'];
            $action = $_GET['action'];
            if ( $action = 'This+post' )
                $imgonly = 0;
            else
                $imgonly = 1;
            delete_post( $no, $pwd, $imgonly, 0, 1, 1 );
            updatelog( $no, $rebuild = 0 );
        default:
            break;
    }
} else
    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
?>
