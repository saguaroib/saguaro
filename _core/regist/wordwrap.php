<?php

// word-wrap without touching things inside of tags
function wordwrap2( $str, $cols, $cut ) {
    // if there's no runs of $cols non-space characters, wordwrap is a no-op
    if ( strlen( $str ) < $cols || !preg_match( '/[^ <>]{' . $cols . '}/', $str ) ) {
        return $str;
    }
    $sections = preg_split( '/[<>]/', $str );
    $str      = '';
    for ( $i = 0; $i < count( $sections ); $i++ ) {
        if ( $i % 2 ) { // inside a tag
            $str .= '<' . $sections[$i] . '>';
        } else { // outside a tag
            $words = explode( ' ', $sections[$i] );
            foreach ( $words as &$word ) {
                $word  = wordwrap( $word, $cols, $cut, 1 );
                // fix utf-8 sequences (XXX: is this slower than mbstring?)
                $lines = explode( $cut, $word );
                for ( $j = 1; $j < count( $lines ); $j++ ) { // all lines except the first
                    while ( 1 ) {
                        $chr = substr( $lines[$j], 0, 1 );
                        if ( ( ord( $chr ) & 0xC0 ) == 0x80 ) { // if chr is a UTF-8 continuation...
                            $lines[$j - 1] .= $chr; // put it on the end of the previous line
                            $lines[$j] = substr( $lines[$j], 1 ); // take it off the current line
                            continue;
                        }
                        break; // chr was a beginning utf-8 character
                    }
                }
                $word = implode( $cut, $lines );

            }
            $str .= implode( ' ', $words );
        }
    }
    return $str;
}

?>