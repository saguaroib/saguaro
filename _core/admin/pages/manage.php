<?php


class SaguaroBoardManagement {
    
    //Display board settings panel
    public function init() {
        if (!valid("manager"))
            error(S_NOPERM);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update();
        } else {
            return $this->display();
        }
    }
    
    private function display() {
        global $csrf, $bannerAssets, $flagAssets;
        require_once(CORE_DIR . "/boards/settings.php");
        $modify = new SaguaroBoardSettings;
        
        $settings = $modify->getJSONConfig();
        
        if (!$settings) {
            $alert = "<h3 class='centered hasReports'>JSON config not found/created yet! Using defaults... You will need to change your settings.</h3> <br>";
            $settings = $modify->getDefaultConfig();
        }
        
        require_once(CORE_DIR . "/boards/config_template.php");
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Board settings";
        $page->headVars['page']['sub'] = "Customize your board!";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");
        
        $dat = "{$alert}";
        
        $dat .= "<div class='centered listContainer container'><form name='change-settings' action='" . PHP_SELF_ABS . "?mode=admin&admin=settings' method='POST'>";
        
        $dat .= $csrf->field();
        
        $general = array(/*"LANGUAGE", */"TITLE", "S_HEADSUB", "S_DESCR");
        $dat .= "<table class='list centered' id='general'>";
        $dat .= "<tr><th class='header' colspan='2'>General settings</th></tr>";
        $dat .= $this->parse($general, $settings, $confDescriptions);
        $dat .= "</table>";
        
        $general = array("INDEXED", "NSFW", "SHOWTITLETXT", "SHOWTITLEIMG", "FILE_BOARD", "STATIC_CATALOG", "ENABLE_ARCHIVE");
        $dat .= "<table class='list centered' id='general'>";
        $dat .= "<tr><th class='header' colspan='2'>General settings</th></tr>";
        $dat .= $this->parse($general, $settings, $confDescriptions);
        $dat .= "</table>";
        
        $posting = array("USE_BBCODE", "DICE_ROLL", "FORTUNE_TRIP", "DISP_ID", "SPOILERS", "NOPICBOX",  "ALLOW_SUBJECT_REPLY",  "COUNTRY_FLAGS");
        $dat .= "<table class='list centered' id='posting'>";
        $dat .= "<tr><th class='header' colspan='2'>Post settings</th></tr>";
        $dat .= $this->parse($posting, $settings, $confDescriptions);
        $dat .= "</table>";
        
        
        $threads = array("NOKO_DEFAULT", "BOTCHECK", "DUPE_CHECK", "REQUIRE_SUBJECT", "SHOW_PERMASAGE", /*"MAX_LINES", "MAX_LINES_SHOWN", "MAX_RES", "MAX_IMGRES", "EVENT_STICKY_RES", "PAGE_MAX"*/);
        $dat .= "<table class='list centered' id='threads'>";
        $dat .= "<tr><th class='header' colspan='2'>Thread settings</th></tr>";
        $dat .= $this->parse($threads, $settings, $confDescriptions);
        $dat .= "</table>";
        
        
        $users = array("SHOW_ADMIN_LOGS", "SHOW_LOG_BANS", "SHOW_ADMIN_NAMES", "JANITOR_CAPCODES");
        $dat .= "<table class='list centered' id='users'>";
        $dat .= "<tr><th class='header' colspan='2'>Users & Administrative settings</th></tr>";
        $dat .= $this->parse($users, $settings, $confDescriptions);
        $dat .= $this->userList();
        $dat .= "</table>";
        /*
        $filters = array("GLOBAL_FILTER", "ENABLE_LOCAL_FILTER");
        $dat .= "<table class='list centered' id='filtering'>";
        $dat .= "<tr><th class='header' colspan='2'>Anti-spam & filters</th></tr>";
        $dat .= $this->parse($filters, $settings, $confDescriptions);
        $dat .= "<tr class='postblock'><td colspan='2'>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=filters'>Manage filters</a>]</td></tr>";
        $dat .= "</table>";*/
        
        $dat .= "<table class='list centered' id='assets'>";
        $dat .= "<tr><th class='header' colspan='2'>Custom assets overview</th></tr>";
        $dat .= "<tr class='row1'><td>" . count(array_keys($flagAssets)) . " flags</td><td>" . count($bannerAssets) . " banners</td></tr>";
        $dat .= "<tr class='postblock'><td colspan='2'>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=assets'>Manage assets</a>]</td></tr>";
        $dat .= "</table>";
        
        $dat .= "<br><br><i>Some settings may not display until a new post is made</i><br><input type='submit' value='Update settings'><br><br></form></div>";
        
        return $page->generate($dat, $nohead = false, $admin = true);
    }
    
    private function update() {
        global $csrf, $mysql;
        
        if ($csrf->validate()) {
            unset($_POST['csrf']); //Validated, safe to dump the value now..
        } else {
            error(S_RELOGIN);
        }

        require_once(CORE_DIR . "/boards/settings.php");
        $modify = new SaguaroBoardSettings;
        
        require_once(CORE_DIR . "/boards/config_template.php");
        
        $settings = array();
        
        foreach($_POST as $key => $value) {
            if (array_key_exists($key, $confBools)) {
                $confBools[$key] = ($_POST[$key] === "on") ? 1 : 0;
            } else {
                if (array_key_exists($key, $confStrings)) {
                    $confStrings[$key] = htmlspecialchars($value);
                } else {
                    error("DING DONG BANNU $key $value");
                }
            }
        }
        
        $settings = array_merge($confBools, $confStrings);
        
        require_once(CORE_DIR . "/boards/settings.php");
        $conf = new SaguaroBoardSettings;
        
        if (!$conf->writeJSONConfig($settings)) {
            error("Error writing config! (301)");
        }
        if (!$conf->writePHPConfig($settings)) {
            error("Error writing config! (302)");
        }
        
        $mysql->query("UPDATE " . SQLBOARDS . " SET indexed='{$confBools['INDEXED']}', public_bans='{$confBools['SHOW_LOG_BANS']}', public_logs='{$confBools['SHOW_ADMIN_LOGS']}', 8archive='{$confBools['ENABLE_ARCHIVE']}', sfw='{$confBools['NSFW']}' WHERE uri='" . BOARD_DIR ."'");
        
        return "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "?mode=admin&admin=settings\">";
    }
    
    private function parse($array, $settings, $description) {
        $j = 0;
        foreach ($array as $key) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr><td class='{$row}'><input name='{$key}' " . $this->getValue($settings[$key]) . " ></td><td class='{$row}'>" . $description[$key] . "</td></tr>";
            ++$j;
        }
        return $temp;
    }
    
    
    private function getValue($value) {
        if (is_numeric($value)) {
            $value = (int) $value;
            return ($value) ? "type='checkbox' value='on' checked" : "type='checkbox' value='on'";
        } else {
            $value = htmlentities($value);
            return "type='text'value='{$value}'";
        }
    }
    
    private function userList() {
        global $mysql;
        
        $users = $mysql->result("SELECT users FROM " . SQLBOARDS . " WHERE uri='" . BOARD_DIR . "'");
        $users = explode(",", $users);
        $temp = "<tr class='postblock'><td colspan='1'></td><td>User</td></tr>";
        
        $j = 1;
        foreach ($users as $user) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr class='{$row} centered'><td>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=users&user={$user}'>Permissions</a>]</td><td>{$user}</td></tr>";
            ++$j; 
        }
        $temp .= "<tr class='postblock' ><td colspan='2'>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=users'>Add/delete users</a>]</td></tr>";
        return $temp;
    }
}