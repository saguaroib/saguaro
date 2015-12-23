<?php

/*

    Autolink aka "I gave up on making a class".
    It's possible but not worth the effort yet.
    
    This could potentially be replicated in Regist's text processors.

*/

/* Auto Linker */
function auto_link( $proto, $resno ) {
    $proto = normalize_links( $proto );
    
    // auto-link remaining URLs if they're not part of HTML
    if ( strpos( $proto, SITE_ROOT ) !== FALSE ) {
        $proto = preg_replace( '/(http:\/\/(?:[A-Za-z]*\.)?)(' . SITE_ROOT . ')(\'' . SITE_SUFFIX . ')(\/)([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?/i', "<a href=\"\\0\" target=\"_blank\">\\0</a>", $proto );
        $proto = preg_replace( '/([<][^>]*?)<a href="((http:\/\/(?:[A-Za-z]*\.)?)(' . SITE_ROOT . ')(\'' . SITE_SUFFIX . ')(\/)([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?)" target="_blank">\\2<\/a>([^<]*?[>])/i', '\\1\\3\\4\\5\\6\\7\\8', $proto );
    }
    
    $proto = intraboard_links( $proto, $resno );
    $proto = interboard_links( $proto );
    return $proto;
}

function normalize_link_cb( $m ) {
    $subdomain = $m[1];
    $original  = $m[0];
    $board     = strtolower( $m[2] );
    $m[0]      = $m[1] = $m[2] = '';
    for ( $i = count( $m ) - 1; $i > 2; $i-- ) {
        if ( $m[$i] ) {
            $no = $m[$i];
            break;
        }
    }
    if ( $subdomain == 'www' || $subdomain == 'static' || $subdomain == 'content' )
        return $original;
    if ( $board == BOARD_DIR )
        return ">>$no";
    else
        return ">>>/$board/$no";
}
function normalize_links( $proto ) {
    // change http://xxx.[[site]/board/res/no links into plaintext >># or >>>/board/#
    if ( strpos( $proto, SITE_ROOT ) === FALSE )
        return $proto;
    
    $proto = preg_replace_callback( '@http://([A-za-z]*)[.]' . SITE_ROOT . '[.]' . SITE_SUFFIX . '/(\w+)/(?:res/(\d+)[.]html(?:#q?(\d+))?|\w+.php[?]res=(\d+)(?:#(\d+))?|)(?=[\s.<!?,]|$)@i', 'normalize_link_cb', $proto );
    // rs.[site].info to >>>rs/query+string
    $proto = preg_replace( '@http://rs[.]' . SITE_ROOT . '[.]' . SITE_SUFFIX . '/\?s=([a-zA-Z0-9$_.+-]+)@i', '>>>/rs/$1', $proto );
    return $proto;
}

function intraboard_link_cb( $m ) {
    global $intraboard_cb_resno, $my_log;
    $my_log->update();
    $no = (int) $m[1];
    $lookup = (int) $my_log->cache[$no]['resto'];
    $resno = $intraboard_cb_resno;
    if ( isset( $lookup ) ) {
        $resto  = $log[$no]['resto'];
        $resdir = ( $resno ? '' : '' );
        $ext    = PHP_EXT;
        if ( $resno && $resno == $resto ) // linking to a reply in the same thread
            return "<a href=\"#$no\" class=\"quotelink\" onClick=\"replyhl('$no');\">>>$no</a>";
        elseif ( $resto == 0 ) // linking to a thread
            return "<a href=\"$resdir$no$ext#$no\" class=\"quotelink\">>>$no</a>";
        else // linking to a reply in another thread
            return "<a href=\"$resdir$resto$ext#$no\" class=\"quotelink\">>>$no</a>";
    }
    return $m[0];
}
function intraboard_links( $proto, $resno ) {
    global $intraboard_cb_resno;
    
    $intraboard_cb_resno = $resno;
    
    $proto = preg_replace_callback( '/>>([0-9]+)/', 'intraboard_link_cb', $proto );
    return $proto;
}

function interboard_link_cb( $m ) {
    // on one hand, we can link to imgboard.php, using any old subdomain, 
    // and let apache & imgboard.php handle it when they click on the link
    // on the other hand, we can use the database to fetch the proper subdomain
    // and even the resto to construct a proper link to the html file (and whether it exists or not)
    
    // for now, we'll assume there's more interboard links posted than interboard links visited.
    $url = DATA_SERVER . $m[1] . '/' . PHP_SELF . ( $m[2] ? ( '?res=' . $m[2] ) : "" );
    return "<a href=\"$url\" class=\"quotelink\">{$m[0]}</a>";
}

/*function interboard_rs_link_cb( $m ) {
    // $m[1] might be a url-encoded query string, or might be manual-typed text
    // so we'll normalize it to raw text first and then re-encode it
    $lsearchquery = urlencode( urldecode( $m[1] ) );
    return "<a href=\"http://rs." . SITE_ROOT . "./?s=$lsearchquery\" class=\"quotelink\">{$m[0]}</a>";
}*/

function interboard_links( $proto ) {
    $boards = "an?|cm?|fa|fit|gif|h[cr]?|[bdefgkmnoprstuvxy]|wg?|ic?|y|cgl|c[ko]|mu|po|t[gv]|toy|test2|trv|jp|r9k|sp";
    $proto  = preg_replace_callback( '@>>>/(' . $boards . ')/([0-9]*)@i', 'interboard_link_cb', $proto );
    //$proto  = preg_replace_callback( '@>>>/rs/([^\s<>]+)@', 'interboard_rs_link_cb', $proto );
    return $proto;
}

?>