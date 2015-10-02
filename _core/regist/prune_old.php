<?php

/*

    This prunes old posts that are pushed off the bottom of last page, called once after each post is made in regist()

    Used exclusively by regist();

*/

function prune_old() {
    global $my_log;
    $my_log->update_cache();

    if (PAGE_MAX >= 1) {
        $maxposts   = LOG_MAX;
        $maxthreads = (PAGE_MAX > 0) ? (PAGE_MAX * PAGE_DEF) : 0;
        //number of pages x how many threads per page

        if ($maxthreads) {
            $exp_order = 'no';
            if (EXPIRE_NEGLECTED == 1)
                $exp_order = 'root';
            $result      = mysql_call("SELECT no FROM " . SQLLOG . " WHERE sticky=0 AND resto=0 ORDER BY $exp_order ASC");
            $threadcount = mysql_num_rows($result);
            while ($row = mysql_fetch_array($result) and $threadcount >= $maxthreads) {
                delete_post($row['no'], 'trim', 0, 1); // imgonly=0, automatic=1, children=1
                $threadcount--;
            }
            mysql_free_result($result);
            // Original max-posts method (note: cleans orphaned posts later than parent posts)
        } else {
            // make list of stickies
            $stickies = array(); // keys are stickied thread numbers
            $result   = mysql_call("SELECT no from " . SQLLOG . " where sticky=1 and resto=0");
            while ($row = mysql_fetch_array($result)) {
                $stickies[$row['no']] = 1;
            }

            $result    = mysql_call("SELECT no,resto,sticky FROM " . SQLLOG . " ORDER BY no ASC");
            $postcount = mysql_num_rows($result);
            while ($row = mysql_fetch_array($result) and $postcount >= $maxposts) {
                // don't delete if this is a sticky thread
                if ($row['sticky'] == 1)
                    continue;
                // don't delete if this is a REPLY to a sticky
                if ($row['resto'] != 0 && $stickies[$row['resto']] == 1)
                    continue;
                delete_post($row['no'], 'trim', 0, 1, 0); // imgonly=0, automatic=1, children=0
                $postcount--;
            }
            mysql_free_result($result);
        }
    }
}

?>
