<?php

/*
    Called by admindel and usrdel to carry out the delete process
    Destroys images and cached pages if CACHE_TTL is enabled
    
*/

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

?>
