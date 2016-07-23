<?php

/*

    Footer class.

    Eventually remove it '</body></html>'.
    Probably around the time the Page generation class is made.

*/

class Footer {
    function format($noFoot = false, $admin = false) {
        $dat = '';

        if ($noFoot) return;
        
        if ($admin) {
            $dat .= $this->adminRibbon();
        }
        
        if (file_exists(BOARDLIST))
            $dat .= '<br><span id="boardNavDesktopFoot">' . file_get_contents(BOARDLIST) . '</span>';

        $dat .= '</div><div class="footer">' . S_FOOT . '</div><a name="bottom"></a></body></html>'; 

        return $dat;
    }
    
    private function adminRibbon() {
        $modes = array("panel", "reports", "appeals", "filters", "assets", "users", "settings", "rebuild", "logout");
        $temp .= "<hr><div class='adminRibbon'>";
        $temp .= "[<a href='/" . BOARD_DIR . "/'>Return</a>] ";
        
        foreach($modes as $mode) 
            if (($mode === 'logout' || $mode === 'panel') || valid($mode)) $temp .= "[<a href='" . PHP_SELF_ABS . "?mode=admin&admin={$mode}'>" . ucfirst($mode) . "</a>] "; //Truly php has functions for everything
            
        $temp .= "<br><hr></div>";
        return $temp;
    }
}

?>
