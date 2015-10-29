<?php

/* text plastic surgery */
function CleanStr( $str ) {
    global $admin;
    $str = trim( $str ); //blankspace removal
    if ( get_magic_quotes_gpc() ) { //magic quotes is deleted (?)
        $str = stripslashes( $str );
    }
    if ( !valid('moderator') ) { //If not moderator+, disable html tags 
        $str = htmlspecialchars( $str ); //remove html special chars
        $str = str_replace( "&amp;", "&", $str ); //remove ampersands
    }
    return str_replace( ",", "&#44;", $str ); //remove commas
}

?>
