<?php 


    function auto_link($comment, $no, $resto) {
		
        //Lookup local (current board) post numbers.
        $temp = preg_replace_callback("/(?:>|&gt;){2}(\d+)/", "lookupLocal", $comment);
        return $temp;
    }
    function lookupLocal($match) {
        global $my_log;
        $me = (int) $match[1];
        $lookup = (int) $my_log->cache[$me]['resto'];
        
        if ($inIndex) {
            if ($lookup > 0) //Replies.
                return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$lookup#$me' class='quotelink' >&gt;&gt;$me</a>";
            else //OPs.
                return "<a href='/" . BOARD_DIR . "/" . RES_DIR . "$me#$me' class='quotelink' >&gt;&gt;$me</a>";
        } else {
            return "<a href='#$me' class='quotelink' >&gt;&gt;$me</a>";
        }
    }