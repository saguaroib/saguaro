<?php

/*
    User permission management class
    aka the embodiment of all php users in a single implementation
    
    return for this when it's not bed time
*/

class SaguaroUserManagement {
    
    private $allPermissions = array(
            "appeals", "assets", "bans", "filters", "reports", "settings", "users", //Admin tools
            "del", "ban", "custommess", "public", //Mod tools
            "flood", "capcode", "textonly",  //Posting options
            "sticky", "lock", "permasage", "cylical", "archive", //Special threads 
            "level" //Permission level
        );
    
    public function init() {
        if (!valid('manager')) 
            error(S_NOPERM);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update();
        } else {
            return $this->display();
        }
    }
    
    private function update() {
        global $csrf;

        /*if (!valid('users'))
            error(S_NOPERM);*/
        
        if (!$csrf->validate())
            error(S_RELOGIN);
        
        if (isset($_POST['username'])) {
            return $this->addUser();
        }
        
        if (isset($_POST['removeFromBoard'])) {
            return $this->deleteUser();
        }
        
        return $this->updateUser();
    }
    
    private function display() {
        global $mysql, $csrf;
        
        require_once(CORE_DIR . "/page/page.php");
        $page = new Page;
        $page->headVars['page']['title'] = "/" . BOARD_DIR . "/ - " . TITLE . " - Manage users";
        $page->headVars['page']['sub'] = "Manage all board staff.";
        $page->headVars['css']['sheet'] = (NSFW) ? array("/stylesheets/admin/nwspanel.css", "/stylesheets/admin/settings.css") : array("/stylesheets/admin/wspanel.css", "/stylesheets/admin/settings.css");
        $page->headVars['css']['raw'] = array(".list {display:inline;}");
        
        if (isset($_GET['user'])) {
            return $page->generate($this->userInfo(), $noHead = false, $admin = true);
        }
        
        $users = $mysql->result("SELECT users FROM " . SQLBOARDS . " WHERE uri='" . BOARD_DIR . "'");
        $users = explode(",", $users);

        $dat .= "<table id='userTable' class='centered'><tr><th class='postblock' colspan='1'></th><th class='postblock'>User</th><th class='postblock' colspan='1'>Last login</th></tr>";
        $j = 0;
        
        require_once(CORE_DIR . "/general/calculate_age.php");
        $age = new CalculateAge;
        
        foreach($users as $user) {
            $row = ($j % 2) ? "row1" : "row2";
            $time = (int) @$mysql->result("SELECT last_login FROM " . SQLMODSLOG . " WHERE username='{$user}'");
            $dat .= "<tr class='{$row}'><td>[<a href='" . PHP_SELF_ABS . "?mode=admin&admin=users&user={$user}'>Manage</a>]</td><td>{$user}</td><td>" . $age->calculate($time) . "</td></tr>";
            ++$j;
        }
        
        $dat .= "</table>";
        
        $dat .= "<form action='" . PHP_SELF_ABS ."?mode=admin&admin=users' method='POST'>";
        $dat .= $csrf->field();
        
        $dat .= "<br><div class='container centered'><div class='header'>Add user</div><br>Username: <input type='text' name='username'><br><br>";
        
        $dat .= $this->tableList();
        
        $dat .= "<br><input type='submit' value='Add user'><br><br>";
        $dat .="</div>";
        $dat .= "</form>";
        
        return $page->generate($dat, $noHead = false, $admin = true);

    }
    
    private function userInfo() {
        global $mysql, $csrf;
        
        $user = $mysql->escape_string($_GET['user']);
        
        $permission = $mysql->result("SELECT permissions FROM " . SQLMODSLOG . " WHERE username='{$user}'");
        $permission = unserialize(base64_decode($permission));
        
        if (!isset($permission[BOARD_DIR])) {
            error("User is not authorized for this board.");
        }
        
        $dat .= "<div class='container centered'><div class='header'>Displaying permissions for user: <span style='text-decoration: underline;'>{$user}</span> on board /" . BOARD_DIR . "/</div>";
        
        $dat .= "<form action='" . PHP_SELF_ABS . "?mode=admin&admin=users' method='POST'>";
        $dat .= $csrf->field();
        $dat .= "<input type='hidden' name='username' value='{$user}'>";
        $dat .= $this->tableList($permission[BOARD_DIR], true);
        
        $dat .= "</form></div>";
        
        return $dat;
        
    }
    
    private function tableList($permar = null, $autoFill = false) { 
        
        $j = 0;
        
        //User permission level
        $userpermArr = array("janitor" => 1, "moderator" => 2, "manager" => 3, "admin" => 4); //Ascending order is important! leave it!
        $temp .= "<br><table id='userLevel' class='list centered'><tr><td  class='postblock header' colspan='2'>User level</td></tr>";
        foreach ($userpermArr as $item => $level) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr class='{$row}'><td><input type='checkbox' value='{$level}' name='level' ".$this->isChecked($permar, 'level', $autoFill)." ></td><td>" . ucfirst($item) . "</td></tr>";
            ++$j;
        }
        $temp .= "</table>";
        
        //Thread modification permissions
        $threadpermArr = array("sticky", "lock", "permasage", "cylical", "archive");
        $temp .= "<table id='userLevel' class='list centered'><tr><td  class='postblock header' colspan='2'>Thread permissions</td></tr>";
        foreach ($threadpermArr as $item) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr class='{$row}'><td><input type='checkbox' value='1' name='{$item}' ".$this->isChecked($permar, $item, $autoFill)." ></td><td>" . ucfirst($item) . "/Un{$item} threads</td></tr>";
            ++$j;
        }
        $temp .= "</table>";
        
        //Posting permissions
        $postpermArr = array("flood", "capcode", "textonly");
        $description = array("flood" => "Bypass flood filters", "capcode" => "Post with capcode", "textonly" => "Bypass thread image requirement");
        $temp .= "<table id='userLevel' class='list centered'><tr><td  class='postblock header' colspan='2'>Posting permissions</td></tr>";
        foreach ($postpermArr as $item) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr class='{$row}'><td><input type='checkbox' value='1' name='{$item}' " . $this->isChecked($permar, $item, $autoFill) . " ></td><td>{$description[$item]}</td></tr>";
            ++$j;
        }
        $temp .= "</table>";
        
        //Moderation tools
        $temp .= "<table id='userLevel' class='list centered'><tr><td  class='postblock header' colspan='2'>Moderation tools</td></tr>";
        $temp .= "<tr class='row1'><td><input type='checkbox' value='1' name='del' " . $this->isChecked($permar, 'del', $autoFill) . " ></td><td>Can delete images only</td></tr>";
        $temp .= "<tr class='row2'><td><input type='checkbox' value='2' name='del' " . $this->isChecked($permar, 'del', $autoFill) . " ></td><td>Can delete posts & images</td></tr>";
        $temp .= "<tr class='row1'><td><input type='checkbox' value='3' name='del' " . $this->isChecked($permar, 'del', $autoFill) . " ></td><td>Can delete all by user IP</td></tr>";
        $temp .= "<tr class='row2'><td><input type='checkbox' value='1' name='ban' " . $this->isChecked($permar, 'ban', $autoFill) . " ></td><td>Can submit ban & warn requests only</td></tr>";
        $temp .= "<tr class='row1'><td><input type='checkbox' value='2' name='ban' " . $this->isChecked($permar, 'ban', $autoFill) . " ></td><td>Can ban & warn users</td></tr>";
        $temp .= "<tr class='row2'><td><input type='checkbox' value='1' name='custommess' " . $this->isChecked($permar, 'custommess', $autoFill) . " ></td><td>Custom ban/warning messages</td></tr>";
        $temp .= "<tr class='row1'><td><input type='checkbox' value='1' name='public' " . $this->isChecked($permar, 'public', $autoFill) . " ></td><td>Public bans/warns</td></tr>";
        $temp .= "</table>";
        
        //Admin tools
        $adminArr = array("appeals", "assets", "bans", "filters", "reports", "settings", "users");
        $temp .= "<table id='userLevel' class='list centered'><tr><td  class='postblock header' colspan='2'>Admin tools</td></tr>";
        foreach ($adminArr as $item) {
            $row = ($j % 2) ? "row1" : "row2";
            $temp .= "<tr class='{$row}'><td><input type='checkbox' value='1' name='{$item}' ".$this->isChecked($permar, $item, $autoFill)." ></td><td>" . ucfirst($item) . " panel access</td></tr>";
            ++$j;    
        }
        $temp .= "</table>";
        
        if ($autoFill) {
            $temp .= "<br><br>";
            $temp .= "<span class='delMsg'>[<input type='checkbox' name='removeFromBoard' value='delete'> Delete user from board? | Please enter your password to confirm: <input type='password' name='removeFromBoardConfirm'>]</span>";
            $temp .= "<br><br><input type='submit' value='Update permissions'><br>";
        }
        
        return $temp;
    }
    
    private function isChecked($perms, $item, $autoFill) {
        return ($autoFill && $perms[$item] >= 1) ? "checked" : null;
    }
    
    private function addUser() {
        global $mysql;
        
        $user = $mysql->escape_string($_POST['username']);
        
        $exists = $mysql->result("SELECT COUNT(username) FROM " . SQLMODSLOG . " WHERE username='{$user}'");
        
        if ($exists < 1) {
            error("That user needs to register first.");
        }

        $board = BOARD_DIR;
        
        $mysql->query("UPDATE " . SQLBOARDS . " SET users=CONCAT(users,',{$user}') WHERE uri='" . BOARD_DIR . "'");
        $mysql->query("UPDATE " . SQLMODSLOG . " SET boards=CONCAT(boards,',{$board}') WHERE username='{$user}'");
        
        $this->updateUser();

        return "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "?mode=admin&admin=users&user={$user}\">"; 
        
    }
    
    private function deleteUser() {
        return "2";
    }
    
    public function updateUser() {
        global $mysql;
        
        $user = $mysql->escape_string($_POST['username']);

        $permission = $mysql->result("SELECT permissions FROM " . SQLMODSLOG . " WHERE username='{$user}'");
        $permission = unserialize(base64_decode($permission));
        unset($permission[BOARD_DIR]);
        
        unset($_POST['admin'], $_POST['username'], $_POST['csrf'],$_POST['removeFromBoardConfirm']);

        
        
        foreach ($_POST as $item => $value) {
            if (!in_array($item, $this->allPermissions)) {
                error("Invalid post detected.");
            }
            if (is_numeric($value)) $permission[BOARD_DIR][$item] = $value;
        }
        
        $permission = base64_encode(serialize($permission));
        $mysql->query("UPDATE " . SQLMODSLOG . " SET permissions='{$permission}' WHERE username='{$user}'");
        
        return "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . PHP_SELF_ABS . "?mode=admin&admin=users&user={$user}\">"; 
    }
    
}