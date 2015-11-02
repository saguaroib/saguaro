<?php

/*

    Handles post linking (>>####).
    Currently does not handle cross-board/etc. since there's no way to properly lookup.

    Doesn't actually use the TextProcessor class, but it does process text!

*/

class AutoLink {
    function format($comment) {
        //Lookup local (current board) post numbers.
        $temp = preg_replace_callback("/(?:>|&gt;){2}(\d+)/", "AutoLink::lookupLocal", $comment);

        //Link to other board's imgboard.php and let it handle the routing.
        $temp = preg_replace("/(?:>|&gt;){2}(\/\w+\/)(\d+)/", "<a href='\\1imgboard.php?res=\\2'>&gt;&gt;\\1\\2", $comment);

        return $temp;
    }

    function lookupLocal($match) {
        global $my_log;
        $my_log->update();
        $me = (int) $match[1];
        $lookup = (int) $my_log->cache[$me]['resto'];

        if ($lookup > 0)
            //Replies.
            return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$lookup#pc$me'>&gt;&gt;$me</a>";
        else
            //OPs.
            return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$me#p$me'>&gt;&gt;$me</a>";
    }
}

?>