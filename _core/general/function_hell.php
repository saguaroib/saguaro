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

if ( !table_exist( SQLLOG ) ) {
    echo ( S_TCREATE . SQLLOG . "<br />" );
    $result = mysql_call( "create table " . SQLLOG . " (primary key(no),
    no    int not null auto_increment,
    now   text,
    name  text,
    email text,
    sub   text,
    com   text,
    host  text,
    pwd   text,
    ext   text,
    w     int,
    h     int,
    tn_w     int,
    tn_h     int,
    tim   text,
    time  int,
    md5   text,
    fsize int,
    fname text,
    sticky int,
    permasage int,
    locked int,
    root  timestamp,
    resto int,
    board text)" );
    
    if ( !$result ) {
        echo S_TCREATEF . SQLLOG . "<br />";
    }
}

if ( !table_exist( SQLBANLOG ) ) {
    echo ( S_TCREATE . SQLBANLOG . "<br />" );
    $result = mysql_call( "create table " . SQLBANLOG . " (
    ip   VARCHAR(25) PRIMARY KEY,
    pubreason  VARCHAR(250),
    staffreason  VARCHAR(250),
    banlength  VARCHAR(250),
    placedOn VARCHAR(50),
    board VARCHAR(50))" );
    
    if ( !$result ) {
        echo S_TCREATEF . SQLBANLOG . "<br />";
    }
}

if ( !table_exist( "reports" ) ) {
    echo ( S_TCREATE . "reports log<br />" );
    $result = mysql_call( "create table reports (		
    no   VARCHAR(25) PRIMARY KEY,		
    reason  VARCHAR(250),		
    ip  VARCHAR(250),		
    board  VARCHAR(250))" );
    
    if ( !$result ) {
        echo S_TCREATEF . "reports log<br />";
    }
}

if ( !table_exist( "loginattempts" ) ) {
    echo ( S_TCREATE . "loginattempts<br />" );
    $result = mysql_call( "create table loginattempts (		
    userattempt   VARCHAR(25) PRIMARY KEY,		
    passattempt  VARCHAR(250),		
    board  VARCHAR(250),		
    ip  VARCHAR(250),		
    attemptno  VARCHAR(50))" );
    
    if ( !$result ) {
        echo S_TCREATEF . "loginattempts<br />";
    }
}

if ( !table_exist( SQLMODSLOG ) ) {
    echo ( S_TCREATE . SQLMODSLOG . "<br />" );
    $result = mysql_call( "create table " . SQLMODSLOG . " (		
    user   VARCHAR(25) PRIMARY KEY,		
    password  VARCHAR(250),		
    allowed  VARCHAR(250),		
    denied  VARCHAR(250))" );
    
    if ( !$result ) {
        echo S_TCREATEF . SQLMODSLOG . "<br />";
    }
    
    mysql_call( "INSERT INTO " . SQLMODSLOG . " (user, password, allowed, denied) VALUES ('admin', 'guest', 'janitor_board,moderator,admin,manager', 'none') " );
    echo "Default account inserted. Username: admin <br> Password: guest.";
}

if ( !table_exist( SQLDELLOG ) ) {
    echo ( S_TCREATE . SQLDELLOG . "<br />" );
    $result = mysql_call( "create table " . SQLDELLOG . " (		
    postno  VARCHAR(250) PRIMARY KEY,
    imgonly   VARCHAR(25),	
    board  VARCHAR(250),		
    name  VARCHAR(250),		
    sub  VARCHAR(50),		
    com VARCHAR(" . S_POSTLENGTH . "),		
    img VARCHAR(250),	
    filename VARCHAR(250),		
    admin VARCHAR(100))" );
    
    if ( !$result ) {
        echo S_TCREATEF . SQLDELLOG . "<br />";
    }
}

?>
