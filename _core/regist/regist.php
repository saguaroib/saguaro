<?php

/*

Eventually rewrite this.

*/

global $path, $badstring, $badfile, $badip, $pwdc, $textonly, $auth;

if ( $pwd == PANEL_PASS )
    $admin = $pwd;
if ( $admin != PANEL_PASS || !valid() )
    $admin = '';
$mes = "";

if ( valid( 'moderator' ) ) {
    $moderator = 1;
    if ( valid( 'admin' ) )
        $moderator = 2;
    if ( valid( 'manager' ) )
        $moderator = 3;
}

if ( SPOILERS == 1 ) {
    $com = spoiler_parse( $com );
}

if ( isset( $_POST['isSticky'] ) || isset( $_POST['isLocked'] ) && valid( 'moderator' ) ) {
    if ( isset( $_POST['isSticky'] ) )
        $stickied = 1;
    if ( isset( $_POST['isLocked'] ) )
        $locked = 1;
}

if ( !$upfile && !$resto ) { // allow textonly threads for moderators!
    if ( valid( 'textonly' ) )
        $textonly = 1;
}

// time
$time = time();
$tim  = $time . substr( microtime(), 2, 3 );

// check closed
$resto = (int) $resto;
if ( $resto ) {
    if ( !$cchk = mysql_call( "select locked from " . SQLLOG . " where no=" . $resto ) ) {
        echo S_SQLFAIL;
    }
    list( $locked ) = mysql_fetch_row( $cchk );
    if ( $locked == 1 && !$admin )
        error( "You can't reply to this thread anymore.", $upfile );
    mysql_free_result( $cchk );
}

// upload processing

$has_image = $upfile && file_exists( $upfile );

if ( $has_image ) {
    // check image limit
    if ( $resto ) {
        if ( !$result = mysql_call( "select COUNT(*) from " . SQLLOG . " where resto=$resto and fsize!=0" ) ) {
            echo S_SQLFAIL;
        }
        $countimgres = mysql_result( $result, 0, 0 );
        if ( $countimgres > MAX_IMGRES )
            error( "Max limit of " . MAX_IMGRES . " image replies has been reached.", $upfile );
        mysql_free_result( $result );
    }
    
    //upload processing
    $dest = tempnam( substr( $path, 0, -1 ), "img" );
    //$dest = $path.$tim.'.tmp';
    if ( OEKAKI_BOARD == 1 && $_POST['oe_chk'] ) {
        rename( $upfile, $dest );
        chmod( $dest, 0644 );
        if ( $pchfile )
            rename( $pchfile, "$dest.pch" );
    } else
        move_uploaded_file( $upfile, $dest );
    
    clearstatcache(); // otherwise $dest looks like 0 bytes!
    
    $upfile_name = CleanStr( $upfile_name );
    $fsize       = filesize( $dest );
    if ( !is_file( $dest ) )
        error( S_UPFAIL, $dest );
    if ( !$fsize /*|| /*$fsize > MAX_KB * 1024*/ )
        error( S_TOOBIG, $dest );
    
    // PDF processing
    if ( ENABLE_PDF == 1 && strcasecmp( '.pdf', substr( $upfile_name, -4 ) ) == 0 ) {
        $ext = '.pdf';
        $W   = $H = 1;
        $md5 = md5_of_file( $dest );
        // run through ghostscript to check for validity
        if ( pclose( popen( "/usr/local/bin/gs -q -dSAFER -dNOPAUSE -dBATCH -sDEVICE=nullpage $dest", 'w' ) ) ) {
            error( S_UPFAIL, $dest );
        }
    } else {
        $size = getimagesize( $dest );
        if ( !is_array( $size ) )
            error( S_NOREC, $dest );
        $md5 = md5_of_file( $dest );
        
        //chmod($dest,0666);
        $W = $size[0];
        $H = $size[1];
        switch ( $size[2] ) {
            case 1:
                $ext = ".gif";
                break;
            case 2:
                $ext = ".jpg";
                break;
            case 3:
                $ext = ".png";
                break;
            case 4:
                $ext = ".swf";
                error( S_UPFAIL, $dest );
                break;
            case 5:
                $ext = ".psd";
                error( S_UPFAIL, $dest );
                break;
            case 6:
                $ext = ".bmp";
                error( S_UPFAIL, $dest );
                break;
            case 7:
                $ext = ".tiff";
                error( S_UPFAIL, $dest );
                break;
            case 8:
                $ext = ".tiff";
                error( S_UPFAIL, $dest );
                break;
            case 9:
                $ext = ".jpc";
                error( S_UPFAIL, $dest );
                break;
            case 10:
                $ext = ".jp2";
                error( S_UPFAIL, $dest );
                break;
            case 11:
                $ext = ".jpx";
                error( S_UPFAIL, $dest );
                break;
            case 13:
                $ext = ".swf";
                error( S_UPFAIL, $dest );
                break;
            default:
                $ext = ".xxx";
                error( S_UPFAIL, $dest );
                break;
        }
        if ( GIF_ONLY == 1 && $size[2] != 1 )
            error( S_UPFAIL, $dest );
    } // end processing -else
    
    // Picture reduction
    if ( !$resto ) {
        $maxw = MAX_W;
        $maxh = MAX_H;
    } else {
        $maxw = MAXR_W;
        $maxh = MAXR_H;
    }
    if ( defined( 'MIN_W' ) && MIN_W > $W )
        error( S_UPFAIL, $dest );
    if ( defined( 'MIN_H' ) && MIN_H > $H )
        error( S_UPFAIL, $dest );
    if ( defined( 'MAX_DIMENSION' ) ) {
        $maxdimension = MAX_DIMENSION;
    } else {
        $maxdimension = 5000;
    }
    if ( $W > $maxdimension || $H > $maxdimension ) {
        error( S_TOOBIGRES, $dest );
    } elseif ( $W > $maxw || $H > $maxh ) {
        $W2 = $maxw / $W;
        $H2 = $maxh / $H;
        ( $W2 < $H2 ) ? $key = $W2 : $key = $H2;
        $TN_W = ceil( $W * $key );
        $TN_H = ceil( $H * $key );
    }
    $mes = $upfile_name . ' ' . S_UPGOOD;
}

if ( $_FILES["upfile"]["error"] > 0 ) {
    if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_INI_SIZE )
        error( S_TOOBIG, $dest );
    if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_FORM_SIZE )
        error( S_TOOBIG, $dest );
    if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_PARTIAL )
        error( S_UPFAIL, $dest );
    if ( $_FILES["upfile"]["error"] == UPLOAD_ERR_CANT_WRITE )
        error( S_UPFAIL, $dest );
}

if ( $upfile_name && $_FILES["upfile"]["size"] == 0 ) {
    error( S_TOOBIGORNONE, $dest );
}

//The last result number
$lastno = mysql_result( mysql_call( "select max(no) from " . SQLLOG ), 0, 0 );

// Number of log lines
if ( !$result = mysql_call( "select no,ext,tim from " . SQLLOG . " where no<=" . ( $lastno - LOG_MAX ) ) ) {
    echo S_SQLFAIL;
} else {
    while ( $resrow = mysql_fetch_row( $result ) ) {
        list( $dno, $dext, $dtim ) = $resrow;
        if ( !mysql_query( "delete from " . SQLLOG . " where no=" . $dno ) ) {
            echo S_SQLFAIL;
        }
        if ( $dext ) {
            if ( is_file( $path . $dtim . $dext ) )
                unlink( $path . $dtim . $dext );
            if ( is_file( THUMB_DIR . $dtim . 's.jpg' ) )
                unlink( THUMB_DIR . $dtim . 's.jpg' );
        }
    }
    mysql_free_result( $result );
}

$find  = false;
$resto = (int) $resto;
if ( $resto ) {
    if ( !$result = mysql_call( "select * from " . SQLLOG . " where root>0 and no=$resto" ) ) {
        echo S_SQLFAIL;
    } else {
        $find = mysql_fetch_row( $result );
        mysql_free_result( $result );
    }
    if ( !$find )
        error( S_NOTHREADERR, $dest );
}

/*	foreach ( $badstring as $value ) {
if ( ereg( $value, $com ) || ereg( $value, $sub ) || ereg( $value, $name ) || ereg( $value, $email ) ) {
error( S_STRREF, $dest );
}
;
}*/
if ( $_SERVER["REQUEST_METHOD"] != "POST" )
    error( S_UNJUST, $dest );
// Form content check
if ( !$name || ereg( "^[ |&#12288;|]*$", $name ) )
    $name = "";
if ( !$com || ereg( "^[ |&#12288;|\t]*$", $com ) )
    $com = "";
if ( !$sub || ereg( "^[ |&#12288;|]*$", $sub ) )
    $sub = "";

if ( !$resto && !$textonly && !is_file( $dest ) )
    error( S_NOPIC, $dest );
if ( !$com && !is_file( $dest ) )
    error( S_NOTEXT, $dest );

$name = ereg_replace( S_MANAGEMENT, "\"" . S_MANAGEMENT . "\"", $name );
$name = ereg_replace( S_DELETION, "\"" . S_DELETION . "\"", $name );

if ( strlen( $com ) > S_POSTLENGTH )
    error( S_TOOLONG, $dest );
if ( strlen( $name ) > 100 )
    error( S_TOOLONG, $dest );
if ( strlen( $email ) > 100 )
    error( S_TOOLONG, $dest );
if ( strlen( $sub ) > 100 )
    error( S_TOOLONG, $dest );
if ( strlen( $resto ) > 10 )
    error( S_UNUSUAL, $dest );
if ( strlen( $url ) > 10 )
    error( S_UNUSUAL, $dest );

//host check
$host  = $_SERVER["REMOTE_ADDR"];
$badip = mysql_call( "SELECT ip FROM " . SQLBANLOG . " WHERE ip = '$host' and banlength <> 0 " );

$query  = mysql_query( "SELECT * FROM " . SQLLOG . " WHERE no=" . $resto );
$result = mysql_fetch_assoc( $query );
if ( $result["locked"] == '1' ) {
    error( S_THREADLOCKED, $dest );
}

//Check if user IP is in bans table
if ( mysql_num_rows( $badip ) == 0 ) {
    // Not Banned
} else {
    //NOW YOU FUCKED UP
    error( S_BADHOST, $dest );
}

if ( eregi( "^mail", $host ) || eregi( "^ns", $host ) || eregi( "^dns", $host ) || eregi( "^ftp", $host ) || eregi( "^prox", $host ) || eregi( "^pc", $host ) || eregi( "^[^\.]\.[^\.]$", $host ) ) {
    $pxck = "on";
}
if ( eregi( "ne\\.jp$", $host ) || eregi( "ad\\.jp$", $host ) || eregi( "bbtec\\.net$", $host ) || eregi( "aol\\.com$", $host ) || eregi( "uu\\.net$", $host ) || eregi( "asahi-net\\.or\\.jp$", $host ) || eregi( "rim\\.or\\.jp$", $host ) ) {
    $pxck = "off";
} else {
    $pxck = "on";
}

if ( $pxck == "on" && PROXY_CHECK ) {
    if ( proxy_connect( '80' ) == 1 ) {
        error( S_PROXY80, $dest );
    } elseif ( proxy_connect( '8080' ) == 1 ) {
        error( S_PROXY8080, $dest );
    }
}

// No, path, time, and url format
srand( (double) microtime() * 1000000 );
if ( $pwd == "" ) {
    if ( $pwdc == "" ) {
        $pwd = rand();
        $pwd = substr( $pwd, 0, 8 );
    } else {
        $pwd = $pwdc;
    }
}

$c_pass = $pwd;
$pass   = ( $pwd ) ? substr( md5( $pwd ), 2, 8 ) : "*";
$youbi  = array(
     S_SUN,
    S_MON,
    S_TUE,
    S_WED,
    S_THU,
    S_FRI,
    S_SAT 
);
$yd     = $youbi[date( "w", $time )];
if ( SHOW_SECONDS == 1 ) {
    $now = date( "m/d/y", $time ) . "(" . (string) $yd . ")" . date( "H:i:s", $time );
} else {
    $now = date( "m/d/y", $time ) . "(" . (string) $yd . ")" . date( "H:i", $time );
}

if ( DISP_ID ) {
    //$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
    //$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
    $color  = "inherit"; // Until unique IDs between threads get sorted out
    $idhtml = "<span id=\"posterid\" style=\"background-color:" . $color . "; border-radius:10px;font-size:8pt;\" />";
    mysql_real_escape_string( $idhtml );
    
    if ( $email && DISP_ID == 1 ) {
        $now .= " (ID:" . $idhtml . " Heaven </span>)";
    } else {
        /* if ( !$resto) {
        //holy hell there has to be a better way to do this. i swear ill think of it soon
        $idsalt = mysql_result( mysql_call( "select max(no) from " . SQLLOG ), 0, 0 ) + 1; //In the year 2054 A.D., your op number is known before you even post it
        } else {
        $idsalt = $resto;
        }*/
        $idsalt = 'id';
        $now .= " (ID:" . $idhtml . substr( crypt( md5( $_SERVER["REMOTE_ADDR"] . 'id' . date( "Ymd", $time ) ), $idsalt ), +3 ) . "</span>)";
    }
}

if ( COUNTRY_FLAGS ) {
    include( "geoiploc.php" );
    $country = getCountryFromIP( $host, "CTRY" );
    $now .= " <img src=" . CSS_PATH . "flags/" . strtolower( $country ) . ".png /> ";
}

$c_name  = $name;
$c_email = $email;

//Text plastic surgery (rorororor)
$email = CleanStr( $email );
$email = ereg_replace( "[\r\n]", "", $email );
$sub   = CleanStr( $sub );
$sub   = ereg_replace( "[\r\n]", "", $sub );
$url   = CleanStr( $url );
$url   = ereg_replace( "[\r\n]", "", $url );
$resto = CleanStr( $resto );
$resto = ereg_replace( "[\r\n]", "", $resto );
$com   = CleanStr( $com, 1 );

if ( SPOILERS == 1 && $spoiler ) {
    $sub = "SPOILER<>$sub";
}
// Standardize new character lines
$com = str_replace( "\r\n", "\n", $com );
$com = str_replace( "\r", "\n", $com );
//$com = preg_replace("/\A([0-9A-Za-z]{10})+\Z/", "!s8AAL8z!", $com);
// Continuous lines
$com = ereg_replace( "\n((&#12288;| )*\n){3,}", "\n", $com );

if ( !$admin && substr_count( $com, "\n" ) > MAX_LINES )
    error( "Error: Too many lines.", $dest );

$com = nl2br( $com ); //br is substituted before newline char

$com = str_replace( "\n", "", $com ); //\n is erased
// Continuous lines
$com = ereg_replace( "\n((&#12288;| )*\n){3,}", "\n", $com );

if ( !$admin && substr_count( $com, "\n" ) > MAX_LINES )
    error( "Error: Too many lines.", $dest );

$name  = ereg_replace( "[\r\n]", "", $name );
$names = iconv( "UTF-8", "CP932//IGNORE", $name ); // convert to Windows Japanese #&#65355;&#65345;&#65357;&#65353;

require_once("tripcode.php");

if ( $email == 'sage' ) {
    $noko  = 0;
    $email = '';
} elseif ( $email == 'nokosage' ) {
    $noko  = 1;
    $email = 'sage';
} elseif ( $email == 'nonoko' ) {
    $noko  = 0;
    $email = '';
} else {
    $noko = 1;
}

if ( $moderator ) {
    if ( $moderator == 1 && isset( $_POST['showCap'] ) )
        $name = '<b><font color="770099">Anonymous ## Mod </font></b>';
    if ( $moderator == 2 && isset( $_POST['showCap'] ) )
        $name = '<b><font color="FF101A">Anonymous ## Admin  </font></b>';
    if ( $moderator == 3 && isset( $_POST['showCap'] ) )
        $name = '<b><font color="2E2EFE">Anonymous ## Manager  </font></b>';
}


if ( !$name )
    $name = S_ANONAME;
if ( !$com )
    $com = S_ANOTEXT;
if ( !$sub )
    $sub = S_ANOTITLE;

if ( FORCED_ANON == 1 ) {
    $name = "</span>$now<span>";
    $sub  = '';
    $now  = '';
}
$com = wordwrap2( $com, 100, "<br />" );
$com = preg_replace( "!(^|>)(&gt;[^<]*)!", "\\1<font class=\"unkfunc\">\\2</font>", $com );

$is_sage = stripos( $email, "sage" ) !== FALSE;


$may_flood = valid( 'floodbypass' );

if ( !$may_flood ) {
    if ( $com ) {
        // Check for duplicate comments
        $query  = "select count(no)>0 from " . SQLLOG . " where com='" . mysql_real_escape_string( $com ) . "' " . "and host='" . mysql_real_escape_string( $host ) . "' " . "and time>" . ( $time - RENZOKU_DUPE );
        $result = mysql_call( $query );
        if ( mysql_result( $result, 0, 0 ) )
            error( S_RENZOKU, $dest );
        mysql_free_result( $result );
    }
    
    if ( !$has_image ) {
        // Check for flood limit on replies
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0";
        $result = mysql_call( $query );
        if ( mysql_result( $result, 0, 0 ) )
            error( S_RENZOKU, $dest );
        mysql_free_result( $result );
    }
    
    if ( $is_sage ) {
        // Check flood limit on sage posts
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU_SAGE ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0 and permasage=1";
        $result = mysql_call( $query );
        if ( mysql_result( $result, 0, 0 ) )
            error( S_RENZOKU, $dest );
        mysql_free_result( $result );
    }
    
    if ( !$resto ) {
        // Check flood limit on new threads
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU3 ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and root>0"; //root>0 == non-sticky
        $result = mysql_call( $query );
        if ( mysql_result( $result, 0, 0 ) )
            error( S_RENZOKU3, $dest );
        mysql_free_result( $result );
    }
}

// Upload processing
if ( $has_image ) {
    if ( !$may_flood ) {
        $query  = "select count(no)>0 from " . SQLLOG . " where time>" . ( $time - RENZOKU2 ) . " " . "and host='" . mysql_real_escape_string( $host ) . "' and resto>0";
        $result = mysql_call( $query );
        if ( mysql_result( $result, 0, 0 ) )
            error( S_RENZOKU2, $dest );
        mysql_free_result( $result );
    }
    
    //Duplicate image check
    if ( DUPE_CHECK ) {
        $result = mysql_call( "select no,resto from " . SQLLOG . " where md5='$md5'" );
        if ( mysql_num_rows( $result ) ) {
            list( $dupeno, $duperesto ) = mysql_fetch_row( $result );
            if ( !$duperesto )
                $duperesto = $dupeno;
            error( '<a href="' . DATA_SERVER . BOARD_DIR . "/res/" . $duperesto . PHP_EXT . '#' . $dupeno . '">' . S_DUPE . '</a>', $dest );
        }
        mysql_free_result( $result );
    }
}

if ( $moderator )
    $host = '[Staff]'; // Don't store mod/admin ips


$rootqu = $resto ? "0" : "now()";
if ( $stickied )
    $rootqu = '20270727070707';
//Bump processing
if ( $resto ) { //sage or age action
    $resline  = mysql_call( "select count(no) from " . SQLLOG . " where resto=" . $resto );
    $countres = mysql_result( $resline, 0, 0 );
    mysql_free_result( $resline );
    $resline = mysql_call( "select sticky,permasage from " . SQLLOG . " where no=" . $resto );
    list( $sticky, $permasage ) = mysql_fetch_row( $resline );
    mysql_free_result( $resline );
    if ( ( stripos( $email, 'sage' ) === FALSE && $countres < MAX_RES && $sticky != "1" && $permasage != "1" ) || ( $admin && $age && $sticky != "1" ) ) {
        $query = "update " . SQLLOG . " set root=now() where no=$resto"; //age
        mysql_call( $query );
    }
}

//Main insert
$query = "insert into " . SQLLOG . " (now,name,email,sub,com,host,pwd,ext,w,h,tn_w,tn_h,tim,time,md5,fsize,fname,sticky,permasage,locked,root,resto) values (" . "'" . $now . "'," . "'" . mysql_real_escape_string( $name ) . "'," . "'" . mysql_real_escape_string( $email ) . "'," . "'" . mysql_real_escape_string( $sub ) . "'," . "'" . mysql_real_escape_string( $com ) . "'," . "'" . mysql_real_escape_string( $host ) . "'," . "'" . mysql_real_escape_string( $pass ) . "'," . "'" . $ext . "'," . (int) $W . "," . (int) $H . "," . (int) $TN_W . "," . (int) $TN_H . "," . "'" . $tim . "'," . (int) $time . "," . "'" . $md5 . "'," . (int) $fsize . "," . "'" . mysql_real_escape_string( $upfile_name ) . "'," . (int) $stickied . "," . (int) $permasage . "," . (int) $locked . "," . $rootqu . "," . (int) mysql_real_escape_string( $resto ) . ")";

if ( !$result = mysql_call( $query ) ) {
    echo S_SQLFAIL;
} //post registration

$cookie_domain = '.' . SITE_ROOT . '';
//Cookies
setrawcookie( "" . SITE_ROOT . "_name", rawurlencode( $c_name ), time() + ( $c_name ? ( 7 * 24 * 3600 ) : -3600 ), '/', $cookie_domain );
if ( ( $c_email != "sage" ) && ( $c_email != "age" ) ) {
    setcookie( "" . SITE_ROOT . "_email", $c_email, time() + ( $c_email ? ( 7 * 24 * 3600 ) : -3600 ), '/', $cookie_domain ); // 1 week cookie expiration
}
setcookie( "" . SITE_ROOT . "_pass", $c_pass, time() + 7 * 24 * 3600, '/', $cookie_domain ); // 1 week cookie expiration


if ( !$resto )
    prune_old();

// thumbnail
if ( $has_image ) {
    rename( $dest, $path . $tim . $ext );
    if ( USE_THUMB ) {
        require_once("thumb.php");
        $tn_name = thumb( $path, $tim, $ext, $resto );
        if ( !$tn_name && $ext != ".pdf" ) {
            error( S_UNUSUAL );
        }
    }
}

$static_rebuild = defined( "STATIC_REBUILD" ) && ( STATIC_REBUILD == 1 );

//Finding the last entry number
if ( !$result = mysql_call( "select max(no) from " . SQLLOG ) ) {
    echo S_SQLFAIL;
}
$hacky    = mysql_fetch_array( $result );
$insertid = (int) $hacky[0];
mysql_free_result( $result );

$deferred = false;
// update html
if ( $resto ) {
    $deferred = updatelog( $resto, $static_rebuild );
} else {
    $deferred = updatelog( $insertid, $static_rebuild );
}

if ( $noko && !$resto ) {
    $redirect = DATA_SERVER . BOARD_DIR . "/res/" . $insertid . PHP_EXT;
} else if ( $noko == 1 ) {
    $redirect = DATA_SERVER . BOARD_DIR . "/res/" . $resto . PHP_EXT . '#' . $insertid;
} else {
    $redirect = PHP_SELF2_ABS;
}

if ( $deferred ) {
    echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"2;URL=$redirect\"></head>";
    echo "<body>$mes " . S_SCRCHANGE . "<br>Your post may not appear immediately.<!-- thread:$resto,no:$insertid --></body></html>";
} else {
    echo "<html><head><META HTTP-EQUIV=\"refresh\" content=\"1;URL=$redirect\"></head>";
    echo "<body>$mes " . S_SCRCHANGE . "<!-- thread:$resto,no:$insertid --></body></html>";
}


?>