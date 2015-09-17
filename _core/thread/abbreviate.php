<?php

// truncate $str to $max_lines lines and return $str and $abbr
// where $abbr = whether or not $str was actually truncated
function abbreviate( $str, $max_lines ) {
    if ( !defined( 'MAX_LINES_SHOWN' ) ) {
        if ( defined( 'BR_CHECK' ) ) {
            define( 'MAX_LINES_SHOWN', BR_CHECK );
        } else {
            define( 'MAX_LINES_SHOWN', 20 );
        }
        $max_lines = MAX_LINES_SHOWN;
    }
    $lines = explode( "<br />", $str );
    if ( count( $lines ) > $max_lines ) {
        $abbr  = 1;
        $lines = array_slice( $lines, 0, $max_lines );
        $str   = implode( "<br />", $lines );
    } else {
        $abbr = 0;
    }
    
    //close spans after abbreviating
    //XXX will not work with more html - use abbreviate_html from shiichan
    $str .= str_repeat( "</span>", substr_count( $str, "<span" ) - substr_count( $str, "</span" ) );
    
    return array(
         $str,
        $abbr 
    );
}

?>