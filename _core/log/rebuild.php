<?php

/*

    Proxy for Rebuild*.

*/

function rebuildqueue_create_table() {
    //Moved into test.php.
}

function rebuildqueue_add( $no ) {
    $board = BOARD_DIR;
    $no    = (int) $no;
    for ( $i = 0; $i < 2; $i++ )
        if ( !mysql_call( "INSERT IGNORE INTO rebuildqueue (board,no) VALUES ('$board','$no')" ) )
            rebuildqueue_create_table();
        else
            break;
}

function rebuildqueue_remove( $no ) {
    $board = BOARD_DIR;
    $no    = (int) $no;
    for ( $i = 0; $i < 2; $i++ )
        if ( !mysql_call( "DELETE FROM rebuildqueue WHERE board='$board' AND no='$no'" ) )
            rebuildqueue_create_table();
        else
            break;
}

function rebuildqueue_take_all() {
    $board = BOARD_DIR;
    $uid   = mt_rand( 1, mt_getrandmax() );
    for ( $i = 0; $i < 2; $i++ )
        if ( !mysql_call( "UPDATE rebuildqueue SET ownedby=$uid,ts=ts WHERE board='$board' AND ownedby=0" ) )
            rebuildqueue_create_table();
        else
            break;
    $q     = mysql_call( "SELECT no FROM rebuildqueue WHERE board='$board' AND ownedby=$uid" );
    $posts = array();
    while ( $post = mysql_fetch_assoc( $q ) )
        $posts[] = $post['no'];
    return $posts;
}

?>