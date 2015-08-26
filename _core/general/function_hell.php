<?php

/*

    These functions are never EVER called, but kept for no reason.

*/

die(''); // No escape

function table_exist( $table ) {
    $result = mysql_call( "show tables like '$table'" );
    if ( !$result ) {
        return 0;
    }
    $a = mysql_fetch_row( $result );
    mysql_free_result( $result );
    return $a;
}

//md5 calculation for earlier than php4.2.0
function md5_of_file( $inFile ) {
    if ( file_exists( $inFile ) ) {
        if ( function_exists( 'md5_file' ) ) {
            return md5_file( $inFile );
        } else {
            $fd           = fopen( $inFile, 'r' );
            $fileContents = fread( $fd, filesize( $inFile ) );
            fclose( $fd );
            return md5( $fileContents );
        }
    } else {
        return false;
    }
}

//check version of gd
function get_gd_ver() {
    if ( function_exists( "gd_info" ) ) {
        $gdver   = gd_info();
        $phpinfo = $gdver["GD Version"];
    } else { //earlier than php4.3.0
        ob_start();
        phpinfo( 8 );
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = strip_tags( $phpinfo );
        $phpinfo = stristr( $phpinfo, "gd version" );
        $phpinfo = stristr( $phpinfo, "version" );
    }
    $end     = strpos( $phpinfo, "." );
    $phpinfo = substr( $phpinfo, 0, $end );
    $length  = strlen( $phpinfo ) - 1;
    $phpinfo = substr( $phpinfo, $length );
    return $phpinfo;
}

function proxy_connect( $port ) {
    $fp = @fsockopen( $_SERVER["REMOTE_ADDR"], $port, $a, $b, 2 );
    if ( !$fp ) {
        return 0;
    } else {
        return 1;
    }
}

?>
