<?php

class SBoard {
    
    function init() {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $this->process();
            return $this->listSettings();
        } else {
            return $this->listSettings();
        }
    }   
     
    function listSettings() {
        global $mysql;

            if (!valid('manager')) error(S_NOPERM);
        
        require_once($_GET['b'] . "/config.php");

        $temp = "<div class='container' style='float:right;margin:5px 5px 0px 0px; ;width:78%;'><div class='header'>Board resources</div>

        
        
        
        </div>";
        
        $temp .= "<div class='container' style='width:20%; margin:5px 0px 0px 5px;'><div class='header'>Settings</div><table><tr><th></th><th>Value</th></tr><form method='POST'>";
        
        $temp .= "<tr><td>Flag your board as NSFW</td><td><input type='checkbox' " . $this->boolToggle(NSFW) . "'></td></tr>";
        $temp .= "<tr><td>Board title</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Board subtitle</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Board description</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Default user name</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Let janitors post with capcodes</td><td><input type='checkbox' " . $this->boolToggle(JANITOR_CAPCODES) . "'></td></tr>";
        $temp .= "<tr><td>Enable BBCode+Markdown</td><td><input type='checkbox' " . $this->boolToggle(USE_BBCODE) . "'></td></tr>";
        $temp .= "<tr><td>Enable Dice rolling</td><td><input type='checkbox' " . $this->boolToggle(DICE_ROLL) . "'></td></tr>";
        $temp .= "<tr><td>Enable #fortune telling</td><td><input type='checkbox' " . $this->boolToggle(FORTUNE_TRIP) . "'></td></tr>";
        $temp .= "<tr><td>Forced Anonymous</td><td><input type='checkbox' " . $this->boolToggle(FORCED_ANON) . "'></td></tr>";
        $temp .= "<tr><td>User IDs</td><td><input type='checkbox' " . $this->boolToggle(DISP_ID) . "'></td></tr>";
        $temp .= "<tr><td>Allow image spoilers</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Allow imageless OPs</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Allow subject field replies</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Require subjects for OPs</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Max replies before autosage</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Max image replies</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Max number cylical replies</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Enable Captcha</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";
        $temp .= "<tr><td>Duplicate image checks</td><td><input type='checkbox' " . $this->boolToggle(DUPE_CHECK) . "'></td></tr>";
        $temp .= "<tr><td>Force user country flag</td><td><input type='checkbox' " . $this->boolToggle(COUNTRY_FLAGS) . "'></td></tr>";
        $temp .= "<tr><td>Display bumplock notification</td><td><input type='checkbox' " . $this->boolToggle(SHOW_PERMASAGE) . "'></td></tr>";
        $temp .= "<tr><td>Admin action logs</td><td><input type='checkbox' " . $this->boolToggle(REPLACEME) . "'></td></tr>";

        
        $temp .= "<tr><td><center><input type='submit' value='Update settings'></center></td></tr></form></table></div>";
        
        
        
        return $temp;

    }
    
    private function boolToggle($in) {
            $temp = ($in) ? 'value="1" checked ' : 'value="0" ';
            return $temp;
    }
}

?>