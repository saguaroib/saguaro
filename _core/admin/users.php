<?php


class SaguaroUserManagement {
    
    public function init() {
        if (!valid('manager')) 
            error(S_NOPERM);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update();
        } else {
            return $this->display();
        }
    }
    
    private function display() {
        global $mysql, $csrf;
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Manage users";
        $page->headVars['page']['sub'] = "Manage all board staff.";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");
        
        if (isset($_GET['user'])) {
            return $page->generate($this->userInfo(), $noHead = false, $admin = true);
        }
        
        $users = $mysql->result("SELECT users FROM " . SQLBOARDS . " WHERE uri='" . BOARD_DIR . "'");
        $users = ltrim($users, ",");
        $users = explode(",", $users);
        
        
        $dat .= "<form action='" . PHP_SELF_ABS ."?mode=admin&admin=users' method='POST'>";
        $dat .= $csrf->field();
        
        
        $dat .= "<table id='userTable' class='centered'><tr><th class='postblock' colspan='1'></th><th class='postblock'>User</th><th class='postblock' colspan='1'>Last login</th></tr>";
        $j = 0;
        
        require_once(CORE_DIR . "/general/calculate_age.php");
        $age = new CalculateAge;
        
        foreach($users as $user) {
            $row = ($j % 2) ? "row1" : "row2";
            $time = (int) @$mysql->result("SELECT last_login FROM " . SQLMODSLOG . " WHERE username='{$user}'");
            $dat .= "<tr class='{$row}'><td>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=users&user={$user}'>Permissions</a>]</td><td>{$user}</td><td>" . $age->calculate($time) . "</td></tr>";
            ++$j;
        }
        
        
        $dat .= "</table>";
        
        $dat .= "<br><div class='container centered'><div class='header'>Add a user</div>Username: <input type='text' name='username'><br>";
        
        
        $dat .= "<table id='userLevel'><tr class='postblock' colspan='2'>User level</tr>";
        $dat .= "<tr><td><input type='radio' value='janitor' name='level'></td><td>Janitor</td></tr>";
        $dat .= "<tr><td><input type='radio' value='moderator' name='level'></td><td>Moderator</td></tr>";
        $dat .= "<tr><td><input type='radio' value='manager' name='level'></td><td>Manager</td></tr>";
        $dat .= "<tr><td><input type='radio' value='admin' name='level'></td><td>Administrator</td></tr>";
        $dat .= "</table>";
        
        /*

        $dat .= "Stickies: <input type='checkbox' name='stickies' value='on'>";
        $dat .= "Lock: <input type='checkbox' name='lock' value='on'>";
        $dat .= "Permasage: <input type='checkbox' name='psage' value='on'>";
        $dat .= "Flood bypass: <input type='checkbox' name='flood' value='on'>";
        $dat .= "Report flood bypass: <input type='checkbox' name='report' value='on'>";
        $dat .= "Can ban: <input type='checkbox' name='bans' value='on'>";
        $dat .= "Custom ban messages: <input type='checkbox' name='customban' value='on'>";
        $dat .= "Public bans: <input type='checkbox' name='pubbans' value='on'>";
        $dat .= "Warns: <input type='checkbox' name='canwarn' value='on'>";
        $dat .= "Custom warn message: <input type='checkbox' name='customwarn' value='on'>";
        $dat .= "Bans: <input type='checkbox' name='bans' value='on'>";*/
        
        
        
        
        
        
        $dat .="</div>";
        
        $dat .= "</form>";
        
        return $page->generate($dat, $noHead = false, $admin = true);

    }
}