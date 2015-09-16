<?php

require('config.php');
include('lang/language.php');

$con  = mysql_connect( SQLHOST, SQLUSER, SQLPASS );

if ( !$con ) {
    echo S_SQLCONF; //unable to connect to DB (wrong user/pass?)
    exit;
}

$db_id = mysql_select_db( SQLDB, $con );
if ( !$db_id ) {
    echo S_SQLDBSF;
}

function head( $dat ) {
	require_once(CORE_DIR . "thread/header.php");
	
	$heading = new PageHead;
	echo $heading->format($dat);
}

function postinfo( $no ) {

    
        if ( !$result = mysql_query( "SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'" ) )
        echo S_SQLFAIL;
    $row = mysql_fetch_row( $result );
    
    list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $resto, $board,  ) = $row;
    
    head( $dat );
    $dat .= "<table border='solid black 2px' border-collapse='collapse' />";
	$dat .= "<tr>[<a href='". PHP_ASELF ."' />Return</a>]</tr><br>";
    $dat .= "<tr><td>Name:</td><td>$name</td></tr>
  <tr><td>Date:</td><td>$now</td></tr>
  <tr><td>IP</td><td>$host</td></tr>
  <tr><td>Comment:</td><td>$com</td></tr>
  <tr><td>MD5:</td><td>$md5</td></tr>
  <tr><td>File</td>";

    if ( $w && $h ) {
        $hasimg = 1;
        $dat .= "<td><img width='" . MAX_W . "' height='" . MAX_H . "' src='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "'/></td></tr>
		<tr><td>Thumbnail:</td><td><img width='" . $tn_w . "' height='" . $tn_h . "' src='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg" . "'/></td></tr>
		<tr><td>Link to file | Link to thumbnail:</td><td><a href='" . DATA_SERVER . BOARD_DIR . "/" . IMG_DIR . $tim . $ext . "' target='_blank' />Image</a> | <a href='" . DATA_SERVER . BOARD_DIR . "/" . THUMB_DIR . $tim . "s.jpg' target='_blank' />Thumb</a></td></tr>";
    } else
        $dat .= "<td>No file</td></tr>";
    
	//<form action='admin.php'/><input type='submit' name='mode' value='test' /></form>
	
	$dat .= "<tr><form action='admin.php' /><td>Delete:</td><td><br />
	<input type='hidden' name='mode' value='delete' />
	<input type='hidden' name='no' value='$no' />
    <input type='submit' name='action' value='This post' /><br />
    <input type='submit' name='action' value='Image only' /><br />
    <input type='submit' name='action' value='All by IP' /><br /></td></tr></table></form>";
	
    if (!$resto) {
        $dat .= "<br /><br /><table><form action='" . DATA_SERVER . BOARD_DIR . "/admin.php' />
        <tr><td>Action</td><td><td><select name='mode' />
        <option value='sticky' />Sticky</option>
        <option value='lock' />Lock</option>
        <option value='permasage' />Permasage</option>
        <option value='unsticky' />Unsticky</option>
        <option value='unlock' />Unlock</option>
        </select></td><td><input type='hidden' name='no' value='$no' /><input type='submit' value='Submit'><td></td></tr></table></form>";
    }
		$dat .= "<tr>[<a href='". PHP_ASELF ."' />Return</a>]</tr><br>";

    echo $dat;

}

function aform( &$post, $resno, $admin = "" ) {
    require_once(CORE_DIR . "/postform.php");

    $postform = new PostForm;
    $post .= $postform->format($resno, $admin);
}

function login( $usernm, $passwd ) {
    $ip     = $_SERVER['REMOTE_ADDR'];
    $usernm = mysql_real_escape_string( $usernm );
    $passwd = mysql_real_escape_string( $passwd );
    
    $query = mysql_call( "SELECT user,password FROM " . SQLMODSLOG . " WHERE user='$usernm' and password='$passwd'" );
    
    if ( $query == 0 or $query == FALSE ) {
        mysql_call( "INSERT INTO loginattempts (userattempt,passattempt,board,ip,attemptno) values('$usernm','$passwd','" . BOARD_DIR . "','$ip','1')" );
        error( S_WRONGPASS );
    }
    
    $hacky  = mysql_fetch_array( $query );
    $usernm = $hacky[0];
    $passwd = $hacky[1];
    
    setcookie( 'saguaro_auser', $usernm, 0 );
    setcookie( 'saguaro_apass', $passwd, 0 );
    
    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . " \">";
}

/*password validation */
function oldvalid( $pass ) {
    
    /*    if ( $pass && $pass != PANEL_PASS )
    error( S_WRONGPASS );*/
    
    if ( valid( 'janitor_board' ) ) {
        head( $dat );
        echo $dat;
        echo "[<a href=\"" . PHP_SELF2 . "\">" . S_RETURNS . "</a>]\n";
        echo "[<a href=\"" . PHP_SELF . "\">" . S_LOGUPD . "</a>]\n";
        if ( valid( 'moderator' ) ) {
            echo "[<a href=\"" . PHP_SELF_ABS . "?mode=rebuild\">Rebuild</a>]\n";
            echo "[<a href=\"" . PHP_SELF_ABS . "?mode=rebuildall\">Rebuild all</a>]\n";
        }
        echo "[<a href=\"" . PHP_ASELF . "?mode=logout\">" . S_LOGOUT . "</a>]\n";
        echo "<div class=\"passvalid\">" . S_MANAMODE . "</div>\n";
        //echo "<form action='" . PHP_SELF . "' method='post' id='contrib' >";
    }

    // Mana login form
    if ( !valid( 'janitor_board' ) ) {
        echo "<p><form action=\"" . PHP_ASELF . "\" method=\"post\">\n";
        echo "<div class=\passvalid\" align=\"center\" vertical-align=\"middle\" >";
        echo "<input type=hidden name=mode value=admin>\n";
        echo "<input type=text name=usernm size=20><br />";
        echo "<input type=password name=passwd size=20><br />";
        echo "<input type=submit value=\"" . S_MANASUB . "\"></form></div>\n";
        if ( isset( $_POST['usernm'] ) && isset( $_POST['passwd'] ) )
            login( $_POST['usernm'], $_POST['passwd'] );
        die( "</body></html>" );
    }
}

/* Admin deletion */
function admindel( $pass ) {
    global $path, $onlyimgdel;
    $delno   = array(
         dummy 
    );
    $delflag = FALSE;
    reset( $_POST );
    while ( $item = each( $_POST ) ) {
        if ( $item[1] == 'delete' ) {
            array_push( $delno, $item[0] );
            $delflag = TRUE;
        }
    }
    if ( $delflag ) {
        if ( !$result = mysql_call( "select * from " . SQLLOG . "" ) ) {
            echo S_SQLFAIL;
        }
        $find = FALSE;
        
        while ( $row = mysql_fetch_row( $result ) ) {
            list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tim, $time, $md5, $fsize,  ) = $row;
            if ( $onlyimgdel == on ) {
                delete_post( $no, $pwd, 1, 1, 1, 0 );
            } else {
                if ( array_search( $no, $delno ) ) { //It is empty when deleting
                    delete_post( $no, $pwd, 0, 1, 1, 0 );
                }
            }
        }
    }
    
    function calculate_age( $timestamp, $comparison = '' ) {
        $units = array(
             'second' => 60,
            'minute' => 60,
            'hour' => 24,
            'day' => 7,
            'week' => 4.25, 
            'month' => 12 
        );
        
        if ( empty( $comparison ) ) {
            $comparison = $_SERVER['REQUEST_TIME'];
        }
        $age_current_unit = abs( $comparison - $timestamp );
        foreach ( $units as $unit => $max_current_unit ) {
            $age_next_unit = $age_current_unit / $max_current_unit;
            if ( $age_next_unit < 1 ) {
                // are there enough of the current unit to make one of the next unit?
                $age_current_unit = floor( $age_current_unit );
                $formatted_age    = $age_current_unit . ' ' . $unit;
                return $formatted_age . ( $age_current_unit == 1 ? '' : 's' );
            }
            $age_current_unit = $age_next_unit;
        }
        
        $age_current_unit = round( $age_current_unit, 1 );
        $formatted_age    = $age_current_unit . ' year';
        return $formatted_age . ( floor( $age_current_unit ) == 1 ? '' : 's' );
        
    }
    
    
    // Deletion screen display
    echo "<input type=hidden name=mode value=admin>\n";
    echo "<input type=hidden name=admin value=del>\n";
    echo "<input type=hidden name=pass value=\"$pass\">\n";
    echo "<div class=\"dellist\">" . S_DELLIST . "</div>\n";
    echo "<div class=\"delbuttons\"><input type=submit value=\"" . S_ITDELETES . "\">";
    echo "<input type=reset value=\"" . S_MDRESET . "\">";
    echo "[<input type=checkbox name=onlyimgdel value=on><!--checked-->" . S_MDONLYPIC . "]</div>";
    echo "<table class=\"postlists\">\n";
    echo "<tr class=\"managehead\">" . S_MDTABLE1;
    echo S_MDTABLE2;
    echo "</tr>\n";
    
    if ( !$result = mysql_call( "select * from " . SQLLOG . " order by no desc" ) ) {
        echo S_SQLFAIL;
    }
    $j = 0;
    while ( $row = mysql_fetch_row( $result ) ) {
        $j++;
        $img_flag = FALSE;
        list( $no, $now, $name, $email, $sub, $com, $host, $pwd, $ext, $w, $h, $tn_w, $tn_h, $tim, $time, $md5, $fsize, $fname, $sticky, $permasage, $locked, $root, $resto ) = $row;
        // Format
        $now = ereg_replace( '.{2}/(.*)$', '\1', $now );
        $now = ereg_replace( '\(.*\)', ' ', $now );
        if ( strlen( $name ) > 10 )
            $truncname = substr( $name, 0, 9 ) . "...";
        else
            $truncname = $name;
        if ( strlen( $sub ) > 10 )
            $truncsub = substr( $sub, 0, 9 ) . "...";
        else
            $truncsub = $sub;
        if ( $email )
            $name = "<a href=\"mailto:$email\">$name</a>";
        $com = str_replace( "<br />", " ", $com );
        $com = htmlspecialchars( $com );
        if ( strlen( $com ) > 20 )
            $trunccom = substr( $com, 0, 18 ) . "...";
        else
            $trunccom = $com;
        if ( strlen( $fname ) > 10 )
            $truncfname = substr( $fname, 0, 40 ) . "..." . $ext;
        else
            $truncfname = $fname;
        // Link to the picture
        if ( $ext && is_file( $path . $tim . $ext ) ) {
            $img_flag = TRUE;
			$clip = "<a class=\"thumbnail\" target=\"_blank\" href=\"".IMG_DIR.$tim.$ext."\">".$tim.$ext."<span><img class='postimg' src=\"".THUMB_DIR.$tim.'s.jpg'."\" width=\"100\" height=\"100\" /></span></a><br />";
            if ( $fsize >= 1048576 ) {
                $size  = round( ( $fsize / 1048576 ), 2 ) . " M";
                $fsize = $asize;
            } else if ( $fsize >= 1024 ) {
                $size  = round( $fsize / 1024 ) . " K";
                $fsize = $asize;
            } else {
                $size  = $fsize . " ";
                $fsize = $asize;
            }
            $all += $asize; //total calculation
            $md5 = substr( $md5, 0, 10 );
        } else {
            $clip = "[No file]";
            $size = 0;
            $md5  = "";
        }
        $class = ( $j % 2 ) ? "row1" : "row2"; //BG color
        
        if ( $resto == '0' ) 
            $resdo = '<b>OP(<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $no . PHP_EXT . '#' . $no . '" target="_blank" />' . $no . '</a>)</b>';
		else
            $resdo = '<a href="' . DATA_SERVER . BOARD_DIR . "/" . RES_DIR . $resto . PHP_EXT . '#' . $no . '" target="_blank" />' . $resto . '</a>';
        $warnSticky = '';
        if ( $sticky == '1' )
            $warnSticky = "<b><font color=\"FF101A\">(Sticky)</font></b>";
        if ( valid( 'janitor_board' ) && !valid( 'moderator' ) ) //Hide IPs from janitors
            $host = '###.###.###.###';
        echo "<tr class=$class><td><input type=checkbox name=\"$no\" value=delete>$warnSticky</td>";
        echo "<td>$no</td><td>$resdo</td><td>$now</td><td>$truncsub</td>";
        echo "<td>$truncname</b></td><td>$trunccom</td>";
        echo "<td class='postimg' >$clip</td><td>" . calculate_age( $time ) . "</td><td><input type=\"button\" text-align=\"center\" onclick=\"location.href='" . PHP_ASELF_ABS . "?mode=more&no=" . $no . "';\" value=\"Post Info\" /></td>\n";
        echo "</tr>\n";
    }
    mysql_free_result( $result );
	
    echo "<br /><br /><link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/img.css' />";
    //foot($dat);
    $all = (int) ( $all / 1024 );
    echo "[ " . S_IMGSPACEUSAGE . $all . "</b> KB ]";
    die( "</body></html>" );
}



function valid( $action = 'moderator', $no = 0 ) {
	require_once("_core/admin/validate.php");
	
	$validate = new Validation;
	$allowed = $validate->verify( $action );
	return $allowed;
}

if (!function_exists(mysql_call)) {
	function mysql_call( $query ) {
		$ret = mysql_query( $query );
		if ( !$ret ) {
			echo "Error on query: " . $query . "<br />";
			echo mysql_error() . "<br />";
		}
		return $ret;
	}
}

function log_cache($invalidate = 0) {
    require_once(CORE_DIR . "/log/log.php");
	
	$my_log = new Log;
    $my_log->update_cache();
    $log = $my_log->cache;
}

// deletes a post from the database
// imgonly: whether to just delete the file or to delete from the database as well
// automatic: always delete regardless of password/admin (for self-pruning)
// children: whether to delete just the parent post of a thread or also delete the children
// die: whether to die on error
// careful, setting children to 0 could leave orphaned posts.
function delete_post( $resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1 ) {
    require_once("_core/log/log.php");
	require_once(CORE_DIR . "admin/delpost.php");
	
	$remove = new DeletePost;
	$remove->targeted( $resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1 );
}

function modify_post ( $action = 'none', $no) {
	if ( !valid( 'moderator' ) )
		die("\"PLEASE AUTOBAN ME FOREVER!!!\" - you");
	switch ( $action ) {
		case 'lock':
            $sqlValue = "locked";
			$sqlBool = 1;
			$verb = "Locked";
			break;
		case 'sticky':
            $rootnum = "2027-07-07 00:00:00";
			$sqlBool = 0;
			$verb = "Unstuck";			
			break;
		case 'permasage':
            $sqlValue = "permasage";
			$sqlBool = 1;
			$verb = "Permanently saged";
			break;
		case 'unlock':
            $sqlValue = "locked";
			$sqlBool = 0;
			$verb = "Unlocked";
		case 'unsticky':
            $rootnum = date('Y-m-d G:i:s');
			$sqlBool = 0;
			$verb = "Unstuck";
			break;
		default:
			break;
	}

	if ( $verb !== "Stuck" || $verb !== "Unstuck" )
		mysql_call( 'UPDATE ' . SQLLOG . " SET " . $sqlValue . "='" . $sqlBool . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
	else 
		mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='". $sqlBool ."' , root='" . $rootnum . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );

    return $verb . " thread $no<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";

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
switch ( $_GET['mode'] ) {
        case 'admin':
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
            break;
        case 'more':
            $no = mysql_real_escape_string($_GET['no']);
            postinfo($no);
            break;
        case 'logout':
            setcookie( 'saguaro_apass', '0', 1 );
            setcookie( 'saguaro_auser', '0', 1 );
            echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF2_ABS . "\">";
            break;
        case 'zmdlog':
            login( $_POST['usernm'], $_POST['passwd'] );
            break;
        case 'lock':
			modify_post( $_GET['no'], "lock");
			break;
        case 'permasage':
			modify_post( $_GET['no'], "permasage");
            break;
        case 'sticky':
			modify_post( $_GET['no'], "sticky");
			break;
        case 'unlock':
			modify_post( $_GET['no'], "unlock");
			break;
        case 'unsticky':
			modify_post( $_GET['no'], "unsticky");
			break;
		case 'delete':
			$no = $_GET['no'];
			$action = $_GET['action'];
			if ( $action = 'This+post') 
				$imgonly = 0;
			else 
				$imgonly = 1;
			delete_post($no, $pwd, $imgonly, 0, 1, 1);
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
		default:
            oldvalid( $pass );
            aform( $post, $res, 1 );
            echo $post;
            echo "<form action=\"" . PHP_ASELF . "\" method=\"post\">
            <input type=hidden name=admin value=del checked>";
            admindel( $pass );
            die( "</body></html>" );
            break;
    }
?>
