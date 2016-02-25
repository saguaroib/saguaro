<?php

/*

    Footer class.

    Eventually remove it '</body></html>'.
    Probably around the time the Page generation class is made.

*/

class Footer {
    function format() {
        $dat = '';

        if (file_exists(BOARDLIST))
            $dat .= '<span class="boardlist">' . file_get_contents(BOARDLIST) . '</span>';

        $dat .= '</div><br><br><div class="footer">' . S_FOOT . '</div><a id="bottom"></a></body></html>'; 

        return $dat;
    }
}

?>
