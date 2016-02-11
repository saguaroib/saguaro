<?php

/*

    Handles post linking (>>####).
    Currently does not handle cross-board/etc. since there's no way to properly lookup.

    Doesn't actually use the TextProcessor class, but it does process text!

*/

class AutoLink {
    function format($comment) {
		
		//meme arrows
		$temp = preg_replace("!(^|>)(>[^<]*)!", "\\1<font class=\"quote\">\\2</font>", $comment);
        //Lookup local (current board) post numbers.
        $temp = preg_replace_callback("/(?:>|>){2}(\d+)/", "AutoLink::lookupLocal", $temp);

        //Link to other board's imgboard.php and let it handle the routing.
        //$temp = preg_replace("/(?:>|>){2}(\/\w+\/)(\d+)/", "<a href='\\1imgboard.php?res=\\2'>>>\\1\\2", $comment); //Revisit, not really essential right now.

        return $temp;
    }

    function lookupLocal($match) {
        global $my_log;
        $my_log->update_cache();
        $me = (int) $match[1];
        $lookup = (int) $my_log->cache[$me]['resto'];

        if ($lookup > 0) //Replies.
            return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$lookup#$me'>>>$me</a>";
        else //OPs.
            return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$me#$me'>>>$me</a>";
    }
}

?>
