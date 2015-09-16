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

function head( $no ) {
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
<span class='boardlist'>" /*. /file_get_contents( BOARDLIST )*/ . " </span>
<span class='adminbar'>
[<a href='" . HOME . "' target='_top'>" . S_HOME . "</a>]
[<a href='" . PHP_ASELF_ABS . "' >" . S_ADMIN . "</a>]
</span><span class='delsettings' style='float:right;'/></span>
<div class='logo'>" . $titlepart . "</div>
<a href='#top' /></a>
<div class='headsub' >" . S_HEADSUB . "</div><hr />";
    
    Echo $dat;
	
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
    $delno   = [];
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
                /*if ( array_search( $no, $delno ) ) { //only a picture is deleted
                $delfile = $path . $tim . $ext; //only a picture is deleted
                if ( is_file( $delfile ) )
                unlink( $delfile ); //delete
                if ( is_file( THUMB_DIR . $tim . 's.jpg' ) )
                unlink( THUMB_DIR . $tim . 's.jpg' ); //delete
                }*/
                delete_post( $no, $pwd, 1, 1, 1, 0 );
            } else {
                if ( array_search( $no, $delno ) ) { //It is empty when deleting
                    delete_post( $no, $pwd, 0, 1, 1, 0 );
                    
                    /*$find = TRUE;
                    if ( !mysql_call( "delete from " . SQLLOG . " where no=" . $no ) ) {
                    echo S_SQLFAIL;
                    }
                    //eat the baby posts too if we kill the parent (OP) post
                    $findchildren = mysql_call( "SELECT * FROM " . SQLLOG . " where  resto=" . $no );
                    if ( mysql_num_rows( $findchildren ) > 0 ) {
                    $eatchildren = mysql_call( "DELETE FROM " . SQLLOG . " where resto=" . $no );
                    mysql_query( $eatchildren );
                    }
                    $delfile = $path . $tim . $ext; //Delete file
                    if ( is_file( $delfile ) )
                    unlink( $delfile ); //Delete
                    if ( is_file( THUMB_DIR . $tim . 's.jpg' ) )
                    unlink( THUMB_DIR . $tim . 's.jpg' ); //Delete*/
                }
            }
        }
        /*		mysql_free_result( $result );
        if ( $find ) { //log renewal
        }*/
    }
    
    function calculate_age( $timestamp, $comparison = '' ) {
        $units = array(
             'second' => 60,
            'minute' => 60,
            'hour' => 24,
            'day' => 7,
            'week' => 4.25, // FUCK YOU GREGORIAN CALENDAR
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
        $now = preg_replace('/.{2}\/(.*)$/', '\\1', $now);
        $now = preg_replace('/\(.*\)/', ' ', $now);
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



//if ( !function_exists( valid ) ) {
	// check whether the current user can perform $action (on $no, for some actions)
	// board-level access is cached in $valid_cache.
	function valid( $action = 'moderator', $no = 0 ) {
		//require_once("_core/admin/validate.php");
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
//}

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

function delete_post( $resno, $pwd, $imgonly = 0, $automatic = 0, $children = 1, $die = 1 ) {
    require_once("_core/log/log.php");

    global $log, $path;
    log_cache();
    $resno = intval( $resno );
    
    // get post info
    if ( !isset( $log[$resno] ) ) {
        if ( $die )
            error( "Can't find the post $resno." );
    }
    $row = $log[$resno];
    
    // check password- if not ok, check admin status (and set $admindel if allowed)
    $delete_ok = ( $automatic || ( substr( md5( $pwd ), 2, 8 ) == $row['pwd'] ) || ( $row['host'] == $_SERVER['REMOTE_ADDR'] ) );
    if ( valid( 'janitor_board' ) ) {
        $delete_ok = $admindel = valid( 'delete', $resno );
    }
    if ( !$delete_ok )
        error( S_BADDELPASS );
    
    // check ghost bumping
    if ( !isset( $admindel ) || !$admindel ) {
        if ( BOARD_DIR == 'a' && (int) $row['time'] > ( time() - 25 ) && $row['email'] != 'sage' ) {
            $ghostdump = var_export( array(
                 'server' => $_SERVER,
                'post' => $_POST,
                'cookie' => $_COOKIE,
                'row' => $row 
            ), true );
            //file_put_contents('ghostbump.'.time(),$ghostdump);
        }
    }
    
    if ( isset( $admindel ) && $admindel ) { // extra actions for admin user
        $auser   = mysql_real_escape_string( $_COOKIE['saguaro_auser'] );
        $adfsize = ( $row['fsize'] > 0 ) ? 1 : 0;
        $adname  = str_replace( '</span> <span class="postertrip">!', '#', $row['name'] );
        if ( $imgonly ) {
            $imgonly = 1;
        } else {
            $imgonly = 0;
        }
        $row['sub']      = mysql_real_escape_string( $row['sub'] );
        $row['com']      = mysql_real_escape_string( $row['com'] );
        $row['filename'] = mysql_real_escape_string( $row['filename'] );
        mysql_call( "INSERT INTO " . SQLDELLOG . " (postno, imgonly, board,name,sub,com,img,filename,admin) values('$resno','$imgonly','" . SQLLOG . "','$adname','{$row['sub']}','{$row['com']}','$adfsize','{$row['filename']}','$auser')" );
    }
    
    if ( $row['resto'] == 0 && $children && !$imgonly ) // select thread and children
        $result = mysql_call( "select no,resto,tim,ext from " . SQLLOG . " where no=$resno or resto=$resno" );
    else // just select the post
        $result = mysql_call( "select no,resto,tim,ext from " . SQLLOG . " where no=$resno" );
    
    while ( $delrow = mysql_fetch_array( $result ) ) {
        // delete
        $delfile  = $path . $delrow['tim'] . $delrow['ext']; //path to delete
        $delthumb = THUMB_DIR . $delrow['tim'] . 's.jpg';
        if ( is_file( $delfile ) )
            unlink( $delfile ); // delete image
        if ( is_file( $delthumb ) )
            unlink( $delthumb ); // delete thumb
        if ( OEKAKI_BOARD == 1 && is_file( $path . $delrow['tim'] . '.pch' ) )
            unlink( $path . $delrow['tim'] . '.pch' ); // delete oe animation
        if ( !$imgonly ) { // delete thread page & log_cache row
            if ( $delrow['resto'] )
                unset( $log[$delrow['resto']]['children'][$delrow['no']] );
            unset( $log[$delrow['no']] );
            $log['THREADS'] = array_diff( $log['THREADS'], array(
                 $delrow['no'] 
            ) ); // remove from THREADS
            mysql_call( "DELETE FROM reports WHERE no=" . $delrow['no'] ); // clear reports
            if ( USE_GZIP == 1 ) {
                @unlink( RES_DIR . $delrow['no'] . PHP_EXT );
                @unlink( RES_DIR . $delrow['no'] . PHP_EXT . '.gz' );
            } else {
                @unlink( RES_DIR . $delrow['no'] . PHP_EXT );
            }
        }
    }
    
    //delete from DB
    if ( $row['resto'] == 0 && $children && !$imgonly ) // delete thread and children
        $result = mysql_call( "delete from " . SQLLOG . " where no=$resno or resto=$resno" );
    elseif ( !$imgonly ) // just delete the post
        $result = mysql_call( "delete from " . SQLLOG . " where no=$resno" );
    
    return $row['resto']; // so the caller can know what pages need to be rebuilt
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
        case 'rebuild':
		    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL='" . PHP_ASELF_ABS . "?mode=rebuild' \">";
            break;
        case 'rebuildall':
		    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL='" . PHP_ASELF_ABS . "?mode=rebuildall' \">";
            break;
        case 'lock':
			$no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Locking thread $no";
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
			break;
        case 'permasage':
			$no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET permasage='1' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
			echo "Permasaging $no";
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
            break;
        case 'sticky':
			$no = $_GET['no'];
            $rootnum = "2027-07-07 00:00:00";
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='1' , root='" . $rootnum . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
			echo "Stickying thread $no";
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
			break;
        case 'unlock':
			$no = $_GET['no'];
            mysql_call( 'UPDATE ' . SQLLOG . " SET locked='0' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Unlocking thread $no";
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
			break;
        case 'unsticky':
			$no = $_GET['no'];
            $rootnum = date('Y-m-d G:i:s');
            mysql_call( 'UPDATE ' . SQLLOG . " SET sticky='0' , root='" . $rootnum . "' WHERE no='" . mysql_real_escape_string( $no ) . "'" );
            echo "Unstickying thread $no";
			echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_ASELF_ABS . "\">";
			break;
		case 'delete':
			include('imgboard.php');
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
