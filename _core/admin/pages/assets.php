<?php

/*
    Saguaro custom board asset management.
    Displays and processes all content for the asset management page
    in the admin panel, handles all things related to uploading and 
    processing custom spoilers, custom flags and board banners.
*/

class SaguaroAssetManagement {
    public function init() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return $this->process();
            } else {
                return $this->display();
            }
    }
    
    //Display the edit page
    public function display() {
        global $csrf;
        
        require_once(CORE_DIR . "/boards/settings.php");
        $modify = new SaguaroBoardSettings;
        
        $settings = $modify->getJSONConfig();
        
        require_once(CORE_DIR . "/boards/config_template.php");
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Custom assets";
        $page->headVars['page']['sub'] = "Customize your board (even more)!";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");
        
        $dat .= "<div class='centered listContainer container'><form name='change-settings' action='" . PHP_SELF_ABS . "?mode=admin' method='POST'>";
        
        $dat .= $csrf->field();
        $dat .= "<input type='hidden' name='admin' value='assets'>";
        
        
        
        $dat .= "<br><br><i>Some changes may not display until a new post is made</i><br><input type='submit' value='Update'><br><br></form></div>";
        
        return $page->generate($dat, $nohead = false, $admin = true);
    }

    
    //Process uploaded assets
    public function process() {
        
    }
}
