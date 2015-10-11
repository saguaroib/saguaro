<?php

/*

    The great reright. It's not write now.
    
*/

//start new tripcode crap
list( $name ) = explode( "#", $name );
$name = CleanStr( $name );

if ( preg_match( "/\#+$/", $names ) ) {
    $names = preg_replace( "/\#+$/", "", $names );
}
if ( preg_match( "/\#/", $names ) ) {
    $names = str_replace( "&#", "&&", htmlspecialchars( $names ) ); // otherwise HTML numeric entities screw up explode()!
    list( $nametemp, $trip, $sectrip ) = str_replace( "&&", "&#", explode( "#", $names, 3 ) );
    $names = $nametemp;
    $name .= "</span>";
    
    if ( $trip != "" ) {
        if ( FORTUNE_TRIP == 1 && $trip == "fortune" ) {
            $fortunes   = array(
                 "Bad Luck",
                "Average Luck",
                "Good Luck",
                "Excellent Luck",
                "Reply hazy, try again",
                "Godly Luck",
                "Very Bad Luck",
                "Outlook good",
                "Better not tell you now",
                "You will meet a dark handsome stranger",
                "&#65399;&#65408;&#9473;&#9473;&#9473;&#9473;&#9473;&#9473;(&#65439;&#8704;&#65439;)&#9473;&#9473;&#9473;&#9473;&#9473;&#9473; !!!!",
                "&#65288;&#12288;´_&#12445;`&#65289;&#65420;&#65392;&#65437; ",
                "Good news will come to you by mail",
                "Hope you're insured",
                "Great things await",
                "Don't leave the house today." 
            );
            $fortunenum = rand( 0, sizeof( $fortunes ) - 1 );
            $fortcol    = "#" . sprintf( "%02x%02x%02x", 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) ), 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) + 2 / 3 * M_PI ), 127 + 127 * sin( 2 * M_PI * $fortunenum / sizeof( $fortunes ) + 4 / 3 * M_PI ) );
            $com        = "<font color=$fortcol><b>Your fortune: " . $fortunes[$fortunenum] . "</b></font><br /><br />" . $com;
            $trip       = "";
            if ( $sectrip == "" ) {
                if ( $name == "</span>" && $sectrip == "" )
                    $name = S_ANONAME;
                else
                    $name = str_replace( "</span>", "", $name );
            }
        } else if ( $trip == "fortune" ) {
            //remove fortune even if FORTUNE_TRIP is off
            $trip = "";
            if ( $sectrip == "" ) {
                if ( $name == "</span>" && $sectrip == "" )
                    $name = S_ANONAME;
                else
                    $name = str_replace( "</span>", "", $name );
            }
            
        } else {
            
            $salt = strtr( preg_replace( "/[^\.-z]/", ".", substr( $trip . "H.", 1, 2 ) ), ":;<=>?@[\\]^_`", "ABCDEFGabcdef" );
            $trip = substr( crypt( $trip, $salt ), -10 );
            $name .= " <span class='name postertrip'>!" . $trip;
        }
    }
    
    
    if ( $sectrip != "" ) {
        $salt = "LOLLOLOLOLOLOLOLOLOLOLOLOLOLOLOL"; //this is ONLY used if the host doesn't have openssl
        //I don't know a better way to get random data
        if ( file_exists( SALTFILE ) ) { //already generated a key
            $salt = file_get_contents( SALTFILE );
        } else {
            system( "openssl rand 448 > '" . SALTFILE . "'", $err );
            if ( $err === 0 ) {
                chmod( SALTFILE, 0400 );
                $salt = file_get_contents( SALTFILE );
            }
        }
        $sha = base64_encode( pack( "H*", sha1( $sectrip . $salt ) ) );
        $sha = substr( $sha, 0, 11 );
        if ( $trip == "" )
            $name .= " <span class='name postertrip'>";
        $name .= "!!" . $sha;
    }
}

?>