<?php

/*

    Footer class.

    Eventually remove it '</body></html>'.
    Probably around the time the Page generation class is made.

*/

class Footer {
    public function format() {
        $dat = '';

        if (file_exists(BOARDLIST))
            $dat .= '<br><span id="boardNavDesktopFoot">' . $this->get_cached_file(BOARDLIST) . '</span>';

        $dat .= '</div><div class="footer">' . S_FOOT . '</div><a id="bottom"></a></body></html>'; 

        return $dat;
    }
    
    //Only read a file once instead of reading it for EACH page rebuild in a single post.
    private function get_cached_file($filename) { 
        static $cache = array();
        if (isset($cache[$filename]))
            return $cache[$filename];
        $cache[$filename] = @file_get_contents($filename);
        return $cache[$filename];
    }
}

?>
