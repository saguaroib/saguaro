<?php
/*

    Needs to be re-written to be class-based.

    For the first step we'll atleast make it modular.

*/

// build a structure out of all the posts in the database.
// this lets us replace a LOT of queries with a simple array access.
// it only builds the first time it was called.
// rather than calling log_cache(1) to rebuild everything,
// you should just manipulate the structure directly.
if ($_SERVER['SCRIPT_FILENAME'] !== __FILE__) { //Only run if the file is not being executed directly.
    global $log, $ipcount, $mysql_unbuffered_reads, $lastno;
    $ips     = array();
    $threads = array(); // no's
    if ( $invalidate == 0 && isset( $log ) )
        return;
    $log = array(); // no -> [ data ]
    mysql_call( "SET read_buffer_size=1048576" );
    $mysql_unbuffered_reads = 1;
    $query                  = mysql_call( "SELECT * FROM " . SQLLOG );
    $offset                 = 0;
    $lastno                 = 0;
    while ( $row = mysql_fetch_assoc( $query ) ) {
        if ( $row['no'] > $lastno )
            $lastno = $row['no'];
        $ips[$row['host']] = 1;
        // initialize log row if necessary
        if ( !isset( $log[$row['no']] ) ) {
            $log[$row['no']]             = $row;
            $log[$row['no']]['children'] = array();
        } else { // otherwise merge it with $row
            foreach ( $row as $key => $val )
                $log[$row['no']][$key] = $val;
        }
        // if this is a reply
        if ( $row['resto'] ) {
            // initialize whatever we need to
            if ( !isset( $log[$row['resto']] ) )
                $log[$row['resto']] = array();
            if ( !isset( $log[$row['resto']]['children'] ) )
                $log[$row['resto']]['children'] = array();

            // add this post to list of children
            $log[$row['resto']]['children'][$row['no']] = 1;
            if ( $row['fsize'] ) {
                if ( !isset( $log[$row['resto']]['imgreplycount'] ) )
                    $log[$row['resto']]['imgreplycount'] = 0;
                else
                    $log[$row['resto']]['imgreplycount']++;
            }
        }

    }

    $query = mysql_call( "SELECT no FROM " . SQLLOG . " WHERE root>0 order by root desc" );
    while ( $row = mysql_fetch_assoc( $query ) ) {
        if ( isset( $log[$row['no']] ) && $log[$row['no']]['resto'] == 0 )
            $threads[] = $row['no'];
    }
    $log['THREADS']         = $threads;
    $mysql_unbuffered_reads = 0;

    // calculate old-status for PAGE_MAX mode
    if ( EXPIRE_NEGLECTED != 1 ) {
        rsort( $threads, SORT_NUMERIC );

        $threadcount = count( $threads );
        if ( PAGE_MAX > 0 ) // the lowest 5% of maximum threads get marked old
            for ( $i = floor( 0.95 * PAGE_MAX * PAGE_DEF ); $i < $threadcount; $i++ ) {
                if ( !$log[$threads[$i]]['sticky'] && EXPIRE_NEGLECTED != 1 )
                    $log[$threads[$i]]['old'] = 1;
            } else { // threads w/numbers below 5% of LOG_MAX get marked old
            foreach ( $threads as $thread ) {
                if ( $lastno - LOG_MAX * 0.95 > $thread )
                    if ( !$log[$thread]['sticky'] )
                        $log[$thread]['old'] = 1;
            }
        }
    }

    $ipcount = count( $ips );
}

?>
