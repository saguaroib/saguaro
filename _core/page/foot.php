<?php

/*

    Footer class.

    Eventually remove it '</body></html>'.
    Probably around the time the Page generation class is made.

*/

class Footer {
    function format() {
        //global $profiler;

        $dat = '';

        if (file_exists(BOARDLIST))
            $dat .= '<span class="boardlist">' . file_get_contents(BOARDLIST) . '</span>';

        $dat .= '<br><br><div class="footer">' . S_FOOT . /*'<br>' . $profiler->total() .*/ '</div><a id="bottom"></a></div></body></html>'; //Last div ends the "afterPosts" class, opened in log.php

        return $dat;
    }
}

?>
